<?php

namespace App\Jobs;

use App\Models\CmEquipment;
use App\Models\CmMonthlyTracking;
use App\Models\CmReading;
use App\Models\ImportLog;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Rap2hpoutre\FastExcel\FastExcel;

class ProcessCmExcelImport implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * Path lengkap file Excel yang akan diproses.
     */
    protected string $filePath;

    /**
     * ID log import untuk tracking progress.
     */
    protected int $importLogId;

    /**
     * ID user yang mengupload.
     */
    protected int $uploadedBy;

    /**
     * Instance ImportLog untuk update progress live.
     */
    protected ?ImportLog $log = null;

    /**
     * Mapping nama kolom Excel ke kolom database untuk Sheet "Data AppSheet".
     */
    private array $readingMapping = [
        'NDEV Motor'            => 'ndev_motor',
        'NDEH Motor'            => 'ndeh_motor',
        'NDEA Motor'            => 'ndea_motor',
        'Temp NDE Motor'        => 'temp_nde_motor',
        'DEV Motor'             => 'dev_motor',
        'DEH Motor'             => 'deh_motor',
        'DEA Motor'             => 'dea_motor',
        'Temp DE Motor'         => 'temp_de_motor',
        'DEV Pompa/Gearbox'     => 'dev_pompa',
        'DEH Pompa/Gearbox'     => 'deh_pompa',
        'DEA Pompa/Gearbox'     => 'dea_pompa',
        'Temp DE Pompa/Gearbox' => 'temp_de_pompa',
        'NDEV Pompa/Gearbox'    => 'ndev_pompa',
        'NDEH Pompa/Gearbox'    => 'ndeh_pompa',
        'NDEA Pompa/Gearbox'    => 'ndea_pompa',
        'Temp NDE Pompa/Gearbox' => 'temp_nde_pompa',
        'DEV Screw'             => 'dev_screw',
        'DEH Screw'             => 'deh_screw',
        'DEA Screw'             => 'dea_screw',
        'Temp DE Screw'         => 'temp_de_screw',
        'NDEV Screw'            => 'ndev_screw',
        'NDEH Screw'            => 'ndeh_screw',
        'NDEA Screw'            => 'ndea_screw',
        'Temp NDE Screw'        => 'temp_nde_screw',
        'Ampere'                => 'ampere',
        'Discharge Pressure'    => 'discharge_pressure',
        'Oil Level'             => 'oil_level',
        'Mech. seal / Gasket Leak' => 'mech_seal',
        'Noise'                 => 'noise',
        'Coupling'              => 'coupling',
        'Safety'                => 'safety',
        'Remark'                => 'remark',
    ];

    /**
     * Mapping status dari Excel ke database.
     */
    private array $statusMapping = [
        'GOOD'       => 'good',
        'ALARM'      => 'alarm',
        'DANGER'     => 'danger',
        'VISUAL BAD' => 'visual_bad',
    ];

    public function __construct(string $filePath, int $importLogId, int $uploadedBy)
    {
        $this->filePath    = $filePath;
        $this->importLogId = $importLogId;
        $this->uploadedBy  = $uploadedBy;
    }

    public function handle(): void
    {
        set_time_limit(600);
        ini_set('memory_limit', '256M');

        $log = ImportLog::findOrFail($this->importLogId);
        $log->update(['status' => 'processing']);
        $this->log = $log;

        $result = [
            'total_dibaca'           => 0,
            'total_diproses'         => 0,
            'total_baris'            => 0,
            'insert_baru'            => 0,
            'update_existing'        => 0,
            'gagal'                  => 0,
            'unregistered'           => 0,
            'total_skipped_duplikat' => 0,
            'total_skipped_kosong'   => 0,
            'unregistered_tags'      => [],
            'errors'                 => [],
            'skipped'                => [],
            'created_reading_ids'    => [],
            'created_equipment_ids'  => [],
            'last_sync'              => 0,
        ];

        try {
            $fastExcel = new FastExcel();
            $appSheet = $fastExcel->withSheetsNames()->importSheets($this->filePath);
            $sheets = $appSheet->toArray();

            if (isset($sheets['Data AppSheet']) && count($sheets['Data AppSheet']) > 0) {
                $result = $this->processDataAppSheet($sheets['Data AppSheet'], $result);
            }
            unset($sheets, $appSheet, $fastExcel);
            gc_collect_cycles();

            $fastExcel2 = new FastExcel();
            $statusCmSheet = $fastExcel2->withSheetsNames()->importSheets($this->filePath);
            $sheets2 = $statusCmSheet->toArray();

            if (isset($sheets2['Status CM']) && count($sheets2['Status CM']) > 0) {
                $result = $this->processStatusCm($sheets2['Status CM'], $result);
            }
            unset($sheets2, $statusCmSheet, $fastExcel2);
            gc_collect_cycles();

            $this->processMonitoringBulananPhpSpreadsheet($result);
            gc_collect_cycles();

            $balanceOk = ($result['total_dibaca'] === $result['total_diproses']);
            if (!$balanceOk) {
                \Illuminate\Support\Facades\Log::warning('IMPORT BALANCE MISMATCH', [
                    'import_log_id' => $this->importLogId,
                    'total_dibaca'  => $result['total_dibaca'],
                    'total_diproses' => $result['total_diproses'],
                    'selisih'       => $result['total_dibaca'] - $result['total_diproses'],
                ]);
            }

            $result['total_baris'] = $result['total_diproses'];

            $log->update([
                'status'                 => 'completed',
                'total_baris'            => $result['total_baris'],
                'total_dibaca'           => $result['total_dibaca'],
                'insert_baru'            => $result['insert_baru'],
                'update_existing'        => $result['update_existing'],
                'gagal'                  => $result['gagal'],
                'unregistered'           => $result['unregistered'],
                'total_skipped_duplikat' => $result['total_skipped_duplikat'],
                'total_skipped_kosong'   => $result['total_skipped_kosong'],
                'detail_unregistered'    => array_values(array_unique($result['unregistered_tags'])),
                'detail_error'           => $result['errors'],
                'detail_skipped'         => $result['skipped'],
                'created_reading_ids'    => $result['created_reading_ids'],
                'created_equipment_ids'  => $result['created_equipment_ids'],
                'completed_at'           => now(),
            ]);
        } catch (\Exception $e) {
            $log->update([
                'status'       => 'failed',
                'detail_error' => [['baris' => 0, 'pesan' => 'Error: ' . $e->getMessage() . ' di ' . $e->getFile() . ':' . $e->getLine()]],
                'completed_at' => now(),
            ]);
        }

        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    private function processDataAppSheet(array $rows, array $result): array
    {
        $barisKe = 0;

        foreach ($rows as $row) {
            $barisKe++;
            $result['total_dibaca']++;

            $row = is_array($row) ? $row : (array) $row;
            $trimmedRow = [];
            foreach ($row as $key => $value) {
                $trimmedRow[trim($key)] = $value;
            }
            $row = $trimmedRow;

            $equipmentTag = trim($row['Equipment Tag'] ?? $row['equipment_tag'] ?? '');
            $dateRaw      = $row['Date'] ?? $row['date'] ?? '';

            if (empty($equipmentTag)) {
                $result['total_skipped_kosong']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $barisKe + 1,
                    'sheet'    => 'Data AppSheet',
                    'kategori' => 'skip_kosong',
                    'pesan'    => 'Equipment Tag kosong',
                ];
                continue;
            }

            if (empty($dateRaw)) {
                $result['total_skipped_kosong']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $barisKe + 1,
                    'sheet'    => 'Data AppSheet',
                    'kategori' => 'skip_kosong',
                    'pesan'    => 'Tanggal kosong untuk equipment ' . $equipmentTag,
                ];
                continue;
            }

            $tanggal = $this->parseDate($dateRaw);
            if (!$tanggal) {
                $result['gagal']++;
                $result['total_diproses']++;
                $result['errors'][] = [
                    'baris' => $barisKe + 1,
                    'sheet' => 'Data AppSheet',
                    'pesan' => 'Tanggal tidak valid: ' . (is_object($dateRaw) ? '(date)' : $dateRaw),
                ];
                continue;
            }

            $equipment = $this->findOrCreateEquipment($row, $equipmentTag, $result);
            if (!$equipment) {
                $result['gagal']++;
                $result['total_diproses']++;
                $result['errors'][] = [
                    'baris' => $barisKe + 1,
                    'sheet' => 'Data AppSheet',
                    'pesan' => 'Gagal menemukan/membuat equipment: ' . $equipmentTag,
                ];
                continue;
            }

            if (!in_array($equipment->id, $result['created_equipment_ids'])) {
                $result['created_equipment_ids'][] = $equipment->id;
            }

            static $seenKeys = [];
            $dupKey = $equipmentTag . '|' . $tanggal;
            if (in_array($dupKey, $seenKeys)) {
                $result['total_skipped_duplikat']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $barisKe + 1,
                    'sheet'    => 'Data AppSheet',
                    'kategori' => 'skip_duplikat',
                    'pesan'    => 'Duplikat dalam file: ' . $equipmentTag . ' + ' . $tanggal,
                ];
                continue;
            }
            $seenKeys[] = $dupKey;

            $readingData = ['cm_equipment_id' => $equipment->id, 'tanggal' => $tanggal];
            foreach ($this->readingMapping as $excelCol => $dbCol) {
                $val = $row[$excelCol] ?? null;
                if ($val !== null && $val !== '') {
                    $readingData[$dbCol] = is_numeric($val) ? (float) $val : $val;
                }
            }

            $statusVal = trim($row['Status'] ?? '');
            if (!empty($statusVal)) {
                $readingData['status'] = $statusVal;
            }

            if (isset($row['Ada_Temuan']) && !empty(trim($row['Ada_Temuan']))) {
                $readingData['remark'] = ($readingData['remark'] ?? '') . ' [Temuan: ' . trim($row['Ada_Temuan']) . ']';
            }

            $existing = CmReading::where('cm_equipment_id', $equipment->id)
                ->where('tanggal', $tanggal)
                ->first();

            if ($existing) {
                $existing->update($readingData);
                $reading = $existing;
            } else {
                $newReading = CmReading::create($readingData);
                $result['created_reading_ids'][] = $newReading->id;
                $reading = $newReading;
                $result['insert_baru']++;
            }

            $this->computeStatusCondition($reading, $row);

            $result['total_diproses']++;
            $this->syncProgress($result);
        }

        $seenKeys = [];
        return $result;
    }

    /**
     * Proses sheet "Status CM".
     */
    private function processStatusCm(array $rows, array $result): array
    {
        $barisKe = 0;

        foreach ($rows as $row) {
            $barisKe++;
            $result['total_dibaca']++;

            $row = is_array($row) ? $row : (array) $row;
            $trimmedRow = [];
            foreach ($row as $key => $value) {
                $trimmedRow[trim($key)] = $value;
            }
            $row = $trimmedRow;

            $equipmentTag = trim($row['Equipment Tag'] ?? '');
            $dateRaw      = $row['Date'] ?? '';

            $rawStatus = trim((string)($row['Status Condition (ISO 10816-3)'] ?? ''));
            if (str_starts_with($rawStatus, '=')) {
                $result['total_skipped_kosong']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $barisKe + 1,
                    'sheet'    => 'Status CM',
                    'kategori' => 'skip_kosong',
                    'pesan'    => 'Data formula (di-skip, nilai dihitung dari Data AppSheet)',
                ];
                continue;
            }

            if (empty($equipmentTag)) {
                $result['total_skipped_kosong']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $barisKe + 1,
                    'sheet'    => 'Status CM',
                    'kategori' => 'skip_kosong',
                    'pesan'    => 'Equipment Tag kosong',
                ];
                continue;
            }

            if (empty($dateRaw)) {
                $result['total_skipped_kosong']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $barisKe + 1,
                    'sheet'    => 'Status CM',
                    'kategori' => 'skip_kosong',
                    'pesan'    => 'Tanggal kosong untuk equipment ' . $equipmentTag,
                ];
                continue;
            }

            $tanggal = $this->parseDate($dateRaw);
            if (!$tanggal) {
                $result['gagal']++;
                $result['total_diproses']++;
                $result['errors'][] = [
                    'baris' => $barisKe + 1,
                    'sheet' => 'Status CM',
                    'pesan' => 'Tanggal tidak valid: ' . (is_object($dateRaw) ? '(date)' : $dateRaw),
                ];
                continue;
            }

            $equipment = CmEquipment::where('equipment_tag', $equipmentTag)->first();
            if (!$equipment) {
                if (!in_array($equipmentTag, $result['unregistered_tags'])) {
                    $result['unregistered_tags'][] = $equipmentTag;
                }
                $result['unregistered']++;
                $result['total_diproses']++;
                continue;
            }

            $kondisi      = $this->mapStatus($rawStatus);
            $maxVibration = $this->parseNumeric($row['Max. Vibration'] ?? $row['max_vibration'] ?? null);
            $maxTemp      = $this->parseNumeric($row['Max. Temp'] ?? $row['max_temp'] ?? null);
            $analisa      = trim($row['Analysis'] ?? '');
            $statusText   = trim($row['Status'] ?? $row['status'] ?? '');
            $visualStatus = trim($row['Mech. seal / Gasket Leak'] ?? '');
            $noise        = trim($row['Noise'] ?? '');
            $coupling     = trim($row['Coupling'] ?? '');
            $safety       = trim($row['Safety Cover'] ?? $row['safety_cover'] ?? '');

            $reading = CmReading::where('cm_equipment_id', $equipment->id)
                ->where('tanggal', $tanggal)
                ->first();

            $updateData = [];
            if ($kondisi) {
                $updateData['kondisi'] = $kondisi;
            }
            if ($maxVibration !== null) {
                $updateData['max_vibration'] = $maxVibration;
            }
            if ($maxTemp !== null) {
                $updateData['max_temp'] = $maxTemp;
            }
            if (!empty($statusText)) {
                $updateData['status'] = $statusText;
            }
            if (!empty($analisa)) {
                $updateData['analysis'] = $analisa;
            }
            if (!empty($visualStatus)) {
                $updateData['mech_seal'] = $visualStatus;
            }
            if (!empty($noise)) {
                $updateData['noise'] = $noise;
            }
            if (!empty($coupling)) {
                $updateData['coupling'] = $coupling;
            }
            if (!empty($safety)) {
                $updateData['safety'] = $safety;
            }

            if (!empty($updateData)) {
                if ($reading) {
                    $reading->update($updateData);
                    $result['update_existing']++;
                } else {
                    $updateData['cm_equipment_id'] = $equipment->id;
                    $updateData['tanggal'] = $tanggal;
                    if (!isset($updateData['kondisi'])) {
                        $updateData['kondisi'] = 'good';
                    }
                    $newReading = CmReading::create($updateData);
                    $result['created_reading_ids'][] = $newReading->id;
                    $result['insert_baru']++;
                }
            }

            $result['total_diproses']++;
            $this->syncProgress($result);
        }

        return $result;
    }

    private function processMonitoringBulananPhpSpreadsheet(array &$result): void
    {
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['Monitoring Bulanan']);

        $spreadsheet = $reader->load($this->filePath);
        $worksheet = $spreadsheet->getSheetByName('Monitoring Bulanan');

        if (!$worksheet) {
            return;
        }

        $highestRow = $worksheet->getHighestRow();
        $highestCol = $worksheet->getHighestColumn();
        $colIndex = Coordinate::columnIndexFromString($highestCol);

        if ($highestRow < 3) {
            return;
        }

        $headers = [];
        for ($col = 1; $col <= $colIndex; $col++) {
            $cellValue = trim((string) $worksheet->getCell([$col, 3])->getValue());
            if (empty($cellValue)) {
                $cellValue = trim((string) $worksheet->getCell([$col, 2])->getValue());
            }
            $headers[$col] = $cellValue;
        }

        $bulanMap = [
            'JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4,
            'MEI' => 5, 'JUN' => 6, 'JUL' => 7, 'AGU' => 8,
            'SEP' => 9, 'OKT' => 10, 'NOV' => 11, 'DES' => 12,
        ];

        $bulanColumns = [];
        $equipmentTagCol = null;

        foreach ($headers as $col => $header) {
            $upper = strtoupper(trim($header));
            if (isset($bulanMap[$upper])) {
                $bulanColumns[$col] = $bulanMap[$upper];
            } elseif (in_array($upper, ['EQUIPMENT TAG', 'EQUIPMENT'])) {
                $equipmentTagCol = $col;
            }
        }

        for ($row = 4; $row <= $highestRow; $row++) {
            $result['total_dibaca']++;

            $equipmentTag = '';
            if ($equipmentTagCol) {
                $equipmentTag = trim((string) $worksheet->getCell([$equipmentTagCol, $row])->getValue());
            }

            if (empty($equipmentTag)) {
                $result['total_skipped_kosong']++;
                $result['total_diproses']++;
                $result['skipped'][] = [
                    'baris'    => $row,
                    'sheet'    => 'Monitoring Bulanan',
                    'kategori' => 'skip_kosong',
                    'pesan'    => 'Equipment Tag kosong di baris ' . $row,
                ];
                continue;
            }

            $equipment = CmEquipment::where('equipment_tag', $equipmentTag)->first();
            if (!$equipment) {
                $result['total_diproses']++;
                continue;
            }

            $tahun = (int) date('Y');

            foreach ($bulanColumns as $col => $bulanAngka) {
                $cellValue = trim((string) $worksheet->getCell([$col, $row])->getValue());
                if (empty($cellValue)) {
                    continue;
                }

                $statusDb = strtolower($cellValue) === 'sudah' ? 'sudah' : 'belum';

                try {
                    CmMonthlyTracking::updateOrCreate(
                        [
                            'cm_equipment_id' => $equipment->id,
                            'tahun'           => $tahun,
                            'bulan'           => $bulanAngka,
                        ],
                        ['status' => $statusDb]
                    );
                } catch (\Exception $e) {
                    $result['gagal']++;
                    $result['errors'][] = [
                        'baris' => $row,
                        'sheet' => 'Monitoring Bulanan',
                        'pesan' => 'Gagal update tracking: ' . $e->getMessage(),
                    ];
                }
            }

            $result['total_diproses']++;
            $this->syncProgress($result);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    /**
     * Update progress ke database secara berkala (max ~2x per detik).
     */
    private function syncProgress(array &$result): void
    {
        if (!$this->log) {
            return;
        }

        $now = microtime(true);
        $lastSync = $result['last_sync'] ?? 0;

        if (($now - $lastSync) < 0.5) {
            return;
        }

        $this->log->update([
            'total_baris'            => $result['total_diproses'] ?? 0,
            'total_dibaca'           => $result['total_dibaca'] ?? 0,
            'insert_baru'            => $result['insert_baru'],
            'update_existing'        => $result['update_existing'],
            'gagal'                  => $result['gagal'],
            'unregistered'           => $result['unregistered'],
            'total_skipped_duplikat' => $result['total_skipped_duplikat'],
            'total_skipped_kosong'   => $result['total_skipped_kosong'],
            'status'                 => 'processing',
        ]);

        $result['last_sync'] = $now;
    }

    /**
     * Cari equipment berdasarkan tag, buat baru jika belum ada.
     */
    private function findOrCreateEquipment(array $row, string $equipmentTag, array &$result): ?CmEquipment
    {
        $equipment = CmEquipment::where('equipment_tag', $equipmentTag)->first();
        if ($equipment) {
            return $equipment;
        }

        if (!in_array($equipmentTag, $result['unregistered_tags'])) {
            $result['unregistered_tags'][] = $equipmentTag;
        }
        $result['unregistered']++;

        $ptLocation = trim($row['PT Location'] ?? $row['pt_location'] ?? '');
        $plant      = trim($row['Plant'] ?? $row['plant'] ?? '');
        $tipeLubrikasi = trim($row['Tipe Lubrikasi'] ?? $row['tipe_lubrikasi'] ?? '');

        try {
            return CmEquipment::create([
                'equipment_tag'  => $equipmentTag,
                'pt_location'    => $ptLocation ?: 'Unknown',
                'plant'          => $plant ?: 'Unknown',
                'tipe_lubrikasi' => $tipeLubrikasi ?: null,
            ]);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value))
                    ->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value);
        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }
        return null;
    }

    private function mapStatus(string $value): ?string
    {
        $upper = strtoupper(trim($value));
        return $this->statusMapping[$upper] ?? null;
    }

    private function computeStatusCondition(CmReading $reading, array $row): void
    {
        $vibrationFields = ['ndev_motor', 'ndeh_motor', 'ndea_motor',
                            'dev_motor', 'deh_motor', 'dea_motor',
                            'dev_pompa', 'deh_pompa', 'dea_pompa',
                            'ndev_pompa', 'ndeh_pompa', 'ndea_pompa',
                            'dev_screw', 'deh_screw', 'dea_screw',
                            'ndev_screw', 'ndeh_screw', 'ndea_screw'];

        $tempFields = ['temp_nde_motor', 'temp_de_motor',
                       'temp_de_pompa', 'temp_nde_pompa',
                       'temp_de_screw', 'temp_nde_screw'];

        $allVibrations = [];
        $allTemps = [];

        $excelToDb = $this->readingMapping;

        foreach ($vibrationFields as $dbField) {
            $excelCol = array_search($dbField, $excelToDb);
            if ($excelCol !== false && isset($row[$excelCol])) {
                $val = $this->parseNumeric($row[$excelCol]);
                if ($val !== null) {
                    $allVibrations[] = $val;
                }
            } elseif ($reading->$dbField !== null) {
                $allVibrations[] = (float) $reading->$dbField;
            }
        }

        foreach ($tempFields as $dbField) {
            $excelCol = array_search($dbField, $excelToDb);
            if ($excelCol !== false && isset($row[$excelCol])) {
                $val = $this->parseNumeric($row[$excelCol]);
                if ($val !== null) {
                    $allTemps[] = $val;
                }
            } elseif ($reading->$dbField !== null) {
                $allTemps[] = (float) $reading->$dbField;
            }
        }

        $maxVibration = !empty($allVibrations) ? max($allVibrations) : null;
        $maxTemp      = !empty($allTemps) ? max($allTemps) : null;

        $adaVisualBad = false;
        foreach (['mech_seal', 'noise', 'coupling', 'safety'] as $field) {
            $excelCol = array_search($field, $excelToDb);
            $val = '';
            if ($excelCol !== false && isset($row[$excelCol])) {
                $val = strtolower(trim((string) $row[$excelCol]));
            } elseif ($reading->$field !== null) {
                $val = strtolower(trim($reading->$field));
            }
            if ($val === 'bad') {
                $adaVisualBad = true;
                break;
            }
        }

        $kondisi = 'good';
        if ($adaVisualBad) {
            $kondisi = 'visual_bad';
        } elseif ($maxVibration !== null && $maxVibration > 7.1) {
            $kondisi = 'danger';
        } elseif ($maxVibration !== null && $maxVibration >= 4.5) {
            $kondisi = 'alarm';
        }

        $updateData = ['kondisi' => $kondisi];
        if ($maxVibration !== null) {
            $updateData['max_vibration'] = round($maxVibration, 2);
        }
        if ($maxTemp !== null) {
            $updateData['max_temp'] = round($maxTemp, 2);
        }

        $reading->update($updateData);
    }
}