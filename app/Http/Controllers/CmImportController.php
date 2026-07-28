<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCmExcelImport;
use App\Models\ImportLog;
use App\Models\CmReading;
use App\Models\CmEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CmImportController extends Controller
{
    public function showImport()
    {
        return redirect()->route('cm.overview');
    }

    public function uploadImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx|max:10240',
        ]);

        $file = $request->file('file');
        $filePath = $file->storeAs(
            'cm-imports',
            'cm-import-' . now()->format('Ymd-His') . '-' . uniqid() . '.xlsx'
        );

        $fullPath = Storage::path($filePath);

        $requiredSheets = ['Data AppSheet', 'Status CM'];
        $zip = new \ZipArchive();
        if ($zip->open($fullPath) === true) {
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $zip->close();

            if ($workbookXml) {
                $xml = simplexml_load_string($workbookXml);
                $sheets = $xml->sheets->sheet ?? [];

                $foundSheets = [];
                foreach ($sheets as $sheet) {
                    $attrs = $sheet->attributes();
                    $foundSheets[] = (string) $attrs['name'];
                }

                $missingSheets = [];
                foreach ($requiredSheets as $required) {
                    if (!in_array($required, $foundSheets)) {
                        $missingSheets[] = $required;
                    }
                }

                if (!empty($missingSheets)) {
                    unlink($fullPath);
                    return response()->json([
                        'success' => false,
                        'message' => 'Sheet ' . implode(', ', $missingSheets) . ' tidak ditemukan.',
                    ], 422);
                }
            }
        } else {
            unlink($fullPath);
            return response()->json([
                'success' => false,
                'message' => 'File tidak dapat dibaca, pastikan format .xlsx valid.',
            ], 422);
        }

        $importLog = ImportLog::create([
            'nama_file'   => $file->getClientOriginalName(),
            'tipe_import' => 'cm_excel',
            'total_baris' => 0,
            'insert_baru' => 0,
            'update_existing' => 0,
            'gagal'       => 0,
            'unregistered' => 0,
            'status'      => 'processing',
            'uploaded_by' => auth()->id(),
        ]);

        ProcessCmExcelImport::dispatch($fullPath, $importLog->id, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload dan sedang diproses di latar belakang.',
            'import_log_id' => $importLog->id,
        ]);
    }

    public function checkStatus(int $id)
    {
        $log = ImportLog::findOrFail($id);

        return response()->json([
            'status'  => $log->status,
            'total_baris'    => $log->total_baris,
            'insert_baru'    => $log->insert_baru,
            'update_existing' => $log->update_existing,
            'gagal'          => $log->gagal,
            'unregistered'   => $log->unregistered,
            'total_skipped_duplikat' => $log->total_skipped_duplikat ?? 0,
            'total_skipped_kosong'   => $log->total_skipped_kosong ?? 0,
            'detail_unregistered' => $log->detail_unregistered ?? [],
            'detail_error'   => $log->detail_error ?? [],
            'completed_at'   => $log->completed_at ? $log->completed_at->format('d M Y H:i:s') : null,
        ]);
    }

    public function history(Request $request)
    {
        $query = ImportLog::with('uploader')
            ->where('tipe_import', 'cm_excel')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('periode')) {
            $hari = match ((int) $request->periode) {
                7 => 7,
                30 => 30,
                90 => 90,
                default => null,
            };
            if ($hari) {
                $query->where('created_at', '>=', now()->subDays($hari));
            }
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('cm.imports-history', compact('logs'));
    }

    public function historyDetail(ImportLog $importLog)
    {
        if ($importLog->tipe_import !== 'cm_excel') {
            abort(404);
        }

        $newEquipments = collect();
        if (!empty($importLog->created_equipment_ids)) {
            $newEquipments = CmEquipment::whereIn('id', $importLog->created_equipment_ids)->get();
        }

        $detailItems = collect();

        if (!empty($importLog->detail_error)) {
            foreach ($importLog->detail_error as $err) {
                $detailItems->push([
                    'baris'    => $err['baris'] ?? '',
                    'sheet'    => $err['sheet'] ?? '-',
                    'kategori' => 'Gagal',
                    'pesan'    => $err['pesan'] ?? '-',
                ]);
            }
        }

        if (!empty($importLog->detail_skipped)) {
            foreach ($importLog->detail_skipped as $skip) {
                $label = match ($skip['kategori'] ?? '') {
                    'skip_duplikat' => 'Skip Duplikat',
                    'skip_kosong'   => 'Skip Kosong',
                    default         => 'Skip',
                };
                $detailItems->push([
                    'baris'    => $skip['baris'] ?? '',
                    'sheet'    => $skip['sheet'] ?? '-',
                    'kategori' => $label,
                    'pesan'    => $skip['pesan'] ?? '-',
                ]);
            }
        }

        if (!empty($importLog->detail_unregistered)) {
            foreach ($importLog->detail_unregistered as $tag) {
                $detailItems->push([
                    'baris'    => '-',
                    'sheet'    => '-',
                    'kategori' => 'Unregistered',
                    'pesan'    => 'Equipment Tag: ' . $tag,
                ]);
            }
        }

        $detailItems = $detailItems->sortBy('baris');

        $totalBreakdown = $importLog->insert_baru
            + $importLog->update_existing
            + $importLog->gagal
            + ($importLog->total_skipped_duplikat ?? 0)
            + ($importLog->total_skipped_kosong ?? 0)
            + $importLog->unregistered;

        $balanceOk = ($importLog->total_dibaca > 0)
            ? ($importLog->total_dibaca === $totalBreakdown)
            : ($importLog->total_baris === $totalBreakdown);

        return view('cm.imports-detail', compact('importLog', 'newEquipments', 'detailItems', 'balanceOk', 'totalBreakdown'));
    }

    public function undoImport(ImportLog $importLog)
    {
        if ($importLog->status !== 'completed') {
            return back()->with('error', 'Hanya import dengan status completed yang bisa di-undo.');
        }

        DB::transaction(function () use ($importLog) {
            if (!empty($importLog->created_reading_ids)) {
                CmReading::whereIn('id', $importLog->created_reading_ids)->delete();
            }

            if (!empty($importLog->created_equipment_ids)) {
                CmEquipment::whereIn('id', $importLog->created_equipment_ids)
                    ->whereDoesntHave('readings')
                    ->delete();
            }

            $importLog->update(['status' => 'undone']);
        });

        return redirect()->route('cm.imports.history')->with('success', 'Import berhasil di-undo.');
    }
}
