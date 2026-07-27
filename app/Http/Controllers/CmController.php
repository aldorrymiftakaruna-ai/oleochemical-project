<?php

namespace App\Http\Controllers;

use App\Exports\CmFindingsExport;
use App\Exports\CmMonitoringExport;
use App\Exports\CmReadingsExport;
use App\Models\CmEquipment;
use App\Models\CmFinding;
use App\Models\CmMonthlyTracking;
use App\Models\CmReading;
use App\Models\Report;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CmController extends Controller
{
    /**
     * Tampilkan halaman Overview Condition Monitoring.
     */
    public function overview(Request $request)
    {
        $filterPt    = $request->get('pt', '');
        $filterTahun = $request->get('tahun', now()->format('Y'));
        $filterBulan = $request->get('bulan', '');
        $filterStatus = $request->get('status', '');

        // Data untuk summary cards
        $totalRecords = CmReading::count();
        $goodCount    = CmReading::where('kondisi', 'good')->count();
        $alarmCount   = CmReading::where('kondisi', 'alarm')->count();
        $dangerCount  = CmReading::where('kondisi', 'danger')->count();
        $visualBadCount = CmReading::where('kondisi', 'visual_bad')->count();

        $goodPct = $totalRecords > 0 ? round(($goodCount / $totalRecords) * 100, 1) : 0;
        $alarmPct = $totalRecords > 0 ? round(($alarmCount / $totalRecords) * 100, 1) : 0;
        $dangerPct = $totalRecords > 0 ? round(($dangerCount / $totalRecords) * 100, 1) : 0;
        $visualBadPct = $totalRecords > 0 ? round(($visualBadCount / $totalRecords) * 100, 1) : 0;

        // Breakdown status per PT untuk donut chart
        $statusPerPt = CmReading::selectRaw('cm_equipment.pt_location, cm_readings.kondisi, COUNT(*) as total')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->groupBy('cm_equipment.pt_location', 'cm_readings.kondisi')
            ->orderBy('cm_equipment.pt_location')
            ->get()
            ->groupBy('pt_location');

        // Trend bulanan stacked
        $trendQuery = CmReading::selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, kondisi, COUNT(*) as total')
            ->groupBy('tahun', 'bulan', 'kondisi')
            ->orderBy('tahun')
            ->orderBy('bulan');

        if ($filterPt) {
            $trendQuery->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }

        $trendData = $trendQuery->get();

        // Top 10 vibrasi tertinggi
        $topVibrasi = CmReading::selectRaw('cm_readings.*, cm_equipment.equipment_tag, cm_equipment.pt_location')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->where('kondisi', 'danger')
            ->orderByRaw('COALESCE(ndev_motor, 0) + COALESCE(ndev_pompa, 0) DESC')
            ->take(10)
            ->get();

        // Daftar PT untuk filter
        $ptList = CmEquipment::select('pt_location')->distinct()->pluck('pt_location');

        // Tahun untuk filter
        $tahunList = CmReading::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('cm.overview', compact(
            'totalRecords', 'goodCount', 'alarmCount', 'dangerCount', 'visualBadCount',
            'goodPct', 'alarmPct', 'dangerPct', 'visualBadPct',
            'statusPerPt', 'trendData', 'topVibrasi',
            'ptList', 'tahunList',
            'filterPt', 'filterTahun', 'filterBulan', 'filterStatus'
        ));
    }

    /**
     * Tampilkan halaman Finding CM.
     */
    public function findings(Request $request)
    {
        $filterPt     = $request->get('pt', '');
        $filterStatus = $request->get('status', '');
        $search       = $request->get('search', '');

        $query = CmFinding::with('equipment');

        if ($filterPt) {
            $query->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('equipment', fn($sq) => $sq->where('equipment_tag', 'like', "%{$search}%"));
            });
        }

        $findings = $query->orderBy('tanggal_temuan', 'desc')->get();

        // Jika request AJAX, return partial view
        if ($request->ajax() || $request->get('ajax')) {
            return view('cm._findings-list', compact('findings'));
        }

        // Summary cards
        $totalOpen      = CmFinding::where('status', 'open')->count();
        $openOver7      = CmFinding::where('status', 'open')->where('hari_open', '>', 7)->count();
        $totalClosed    = CmFinding::where('status', 'closed')->count();
        $totalFindings  = CmFinding::count();

        $ptList = CmEquipment::select('pt_location')->distinct()->pluck('pt_location');

        return view('cm.findings', compact(
            'findings', 'ptList',
            'filterPt', 'filterStatus', 'search',
            'totalOpen', 'openOver7', 'totalClosed', 'totalFindings'
        ));
    }

    /**
     * Tampilkan halaman CM Monitoring.
     */
    public function monitoring(Request $request)
    {
        $filterTahun = $request->get('tahun', now()->format('Y'));
        $hideDone    = $request->boolean('hide_done', false);

        $tahunIni = (int) $filterTahun;
        $bulanIni = (int) now()->format('n');

        // Semua equipment
        $equipments = CmEquipment::with(['monthlyTrackings' => fn($q) => $q->where('tahun', $tahunIni)])
            ->orderBy('pt_location')
            ->orderBy('equipment_tag')
            ->get();

        // Hitung progress per equipment
        $equipments->each(function ($eq) use ($tahunIni, $bulanIni) {
            $totalBulan = $tahunIni < now()->year ? 12 : $bulanIni;
            $sudahCount = $eq->monthlyTrackings->where('status', 'sudah')->count();
            $eq->progress_pct = $totalBulan > 0 ? round(($sudahCount / $totalBulan) * 100, 0) : 0;
            $eq->sudah_count = $sudahCount;
            $eq->total_bulan = $totalBulan;
        });

        // Kelompokkan alert: equipment yang belum ada data bulan berjalan
        $alertEquipments = CmEquipment::whereDoesntHave('monthlyTrackings', function ($q) use ($tahunIni, $bulanIni) {
            $q->where('tahun', $tahunIni)->where('bulan', $bulanIni)->where('status', 'sudah');
        })->with(['monthlyTrackings' => fn($q) => $q->where('tahun', $tahunIni)->orderBy('bulan', 'desc')])
            ->get();

        // Data terakhir bulan apa per equipment untuk alert
        $alertEquipments->each(function ($eq) {
            $lastTrack = $eq->monthlyTrackings->first();
            $eq->last_month_label = $lastTrack ? $this->bulanLabel($lastTrack->bulan) : 'Belum pernah ada data';
        });

        // Filter hide done
        if ($hideDone) {
            $equipments = $equipments->filter(fn($eq) => $eq->progress_pct < 100);
        }

        $ptList = CmEquipment::select('pt_location')->distinct()->pluck('pt_location');
        $tahunList = range(now()->year - 2, now()->year);

        return view('cm.monitoring', compact(
            'equipments', 'ptList', 'tahunList',
            'filterTahun', 'hideDone', 'alertEquipments',
            'tahunIni', 'bulanIni'
        ));
    }

    /**
     * Tampilkan halaman Equipment Detail.
     */
    public function equipmentDetail(Request $request, $id = null)
    {
        $equipment = null;
        $readings  = collect();

        if ($id) {
            $equipment = CmEquipment::with(['readings' => fn($q) => $q->orderBy('tanggal')])->findOrFail($id);
            $readings = $equipment->readings;
        }

        $equipments = CmEquipment::orderBy('equipment_tag')->get();

        return view('cm.equipment-detail', compact('equipment', 'readings', 'equipments'));
    }

    /**
     * Tampilkan halaman detail equipment berdasarkan tag.
     * Route /cm/equipment/{tag} dengan filter rentang waktu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $tag
     * @return \Illuminate\View\View
     */
    public function equipmentShow(Request $request, string $tag)
    {
        $range = $request->get('range', '30'); // default 30 hari

        $equipment = CmEquipment::with(['asset.company', 'findings'])
            ->where('equipment_tag', $tag)
            ->firstOrFail();

        // Query readings dengan filter tanggal
        $filteredQuery = $equipment->readings()
            ->orderBy('tanggal', 'desc');

        if ($range !== 'all') {
            $days = (int) $range;
            $filteredQuery->where('tanggal', '>=', now()->subDays($days));
        }

        // Ambil semua readings yang difilter untuk data ringkasan
        $filteredReadings = $filteredQuery->get();

        // Data ringkasan dari last reading
        $lastReading = $filteredReadings->first();
        $equipment->current_status = $lastReading?->kondisi ?? 'good';

        // Vibrasi & temperatur maks dari last reading
        $vibMax = '-';
        $tempMax = '-';
        if ($lastReading) {
            $vibValues = array_filter([
                $lastReading->ndev_motor, $lastReading->ndeh_motor, $lastReading->ndea_motor,
                $lastReading->dev_motor,  $lastReading->deh_motor,  $lastReading->dea_motor,
                $lastReading->ndev_pompa, $lastReading->ndeh_pompa, $lastReading->ndea_pompa,
                $lastReading->dev_pompa,  $lastReading->deh_pompa,  $lastReading->dea_pompa,
                $lastReading->ndev_screw, $lastReading->ndeh_screw, $lastReading->ndea_screw,
                $lastReading->dev_screw,  $lastReading->deh_screw,  $lastReading->dea_screw,
            ], fn($v) => $v !== null);
            if (count($vibValues) > 0) {
                $maxV = max($vibValues);
                $vibMax = $maxV > 0 ? number_format($maxV, 1) : '-';
            }

            $tempValues = array_filter([
                $lastReading->temp_de_motor, $lastReading->temp_nde_motor,
                $lastReading->temp_de_pompa, $lastReading->temp_nde_pompa,
                $lastReading->temp_de_screw, $lastReading->temp_nde_screw,
            ], fn($t) => $t !== null);
            if (count($tempValues) > 0) {
                $maxT = max($tempValues);
                $tempMax = $maxT > 0 ? number_format($maxT, 1) : '-';
            }
        }

        // Data JSON untuk Chart.js (sort ascending by tanggal)
        $chartData = $filteredReadings->sortBy('tanggal')->values()->map(fn($r) => [
            'tanggal'    => $r->tanggal->format('Y-m-d'),
            'ndev_motor' => $r->ndev_motor,
            'ndeh_motor' => $r->ndeh_motor,
            'ndea_motor' => $r->ndea_motor,
            'dev_motor'  => $r->dev_motor,
            'deh_motor'  => $r->deh_motor,
            'dea_motor'  => $r->dea_motor,
            'ndev_pompa' => $r->ndev_pompa,
            'ndeh_pompa' => $r->ndeh_pompa,
            'ndea_pompa' => $r->ndea_pompa,
        ]);

        // Readings dengan pagination (10 per halaman) — tetap difilter
        $paginatedQuery = $equipment->readings()
            ->orderBy('tanggal', 'desc');

        if ($range !== 'all') {
            $paginatedQuery->where('tanggal', '>=', now()->subDays((int) $range));
        }

        $readings = $paginatedQuery->paginate(10)->appends(['range' => $range]);

        // Hitung total findings open/closed
        $totalFindingsOpen   = $equipment->findings->where('status', 'open')->count();
        $totalFindingsClosed = $equipment->findings->where('status', 'closed')->count();

        // ------------------------------------------------------------------
        // MTBF, Total Failures & Last Failure — dari tabel Reports (Laporan)
        // Sumber: Report dengan jenis_pekerjaan IN ('CM','dCM') untuk
        // equipment ini, dalam rentang filter yang aktif.
        // ------------------------------------------------------------------
        $mtbfData = [
            'mtbf'          => '-',
            'total_failures' => 0,
            'last_failure'   => null,
            'days_since'     => null,
        ];

        $failureReportsQuery = Report::where('equipment_tag', $equipment->equipment_tag)
            ->whereIn('jenis_pekerjaan', ['CM', 'dCM']);

        // Terapkan filter rentang waktu
        if ($range !== 'all') {
            $days = (int) $range;
            $failureReportsQuery->where(function ($q) use ($days) {
                $q->where('tanggal_kejadian', '>=', now()->subDays($days))
                  ->orWhere(function ($sq) use ($days) {
                      $sq->whereNull('tanggal_kejadian')
                         ->where('report_date', '>=', now()->subDays($days));
                  });
            });
        }

        $failureReports = $failureReportsQuery
            ->orderByRaw('COALESCE(tanggal_kejadian, report_date) ASC')
            ->get(['tanggal_kejadian', 'report_date']);

        if ($failureReports->isNotEmpty()) {
            $totalFailures = $failureReports->count();

            // Last failure = tanggal_kejadian terakhir (atau report_date jika null)
            $lastReport  = $failureReports->last();
            $lastFailure = $lastReport->tanggal_kejadian ?? $lastReport->report_date;
            $daysSince   = $lastFailure ? now()->startOfDay()->diffInDays($lastFailure) : null;

            // Hitung MTBF
            $dates = $failureReports->map(function ($r) {
                return $r->tanggal_kejadian ?? $r->report_date;
            })->filter()->sort()->values();

            if ($dates->count() >= 2) {
                $diffs = [];
                for ($i = 1; $i < $dates->count(); $i++) {
                    $diffs[] = $dates[$i]->diffInDays($dates[$i - 1]);
                }
                $mtbf = count($diffs) > 0 ? array_sum($diffs) / count($diffs) : 0;
            } else {
                // Hanya 1 failure — MTBF = jarak dari tanggal failure ke sekarang
                $mtbf = $dates->first() ? now()->startOfDay()->diffInDays($dates->first()) : 0;
            }

            $mtbfData = [
                'mtbf'           => round($mtbf, 1),
                'total_failures' => $totalFailures,
                'last_failure'   => $lastFailure ? $lastFailure->format('d M Y') : null,
                'days_since'     => $daysSince,
            ];
        }

        // ------------------------------------------------------------------
        // Insight Trend Vibrasi Motor — deteksi slope/kenaikan signifikan
        // ------------------------------------------------------------------
        $vibrationInsight = $this->detectVibrationTrend($chartData);

        return view('cm.equipment-show', compact(
            'equipment',
            'lastReading',
            'vibMax',
            'tempMax',
            'chartData',
            'readings',
            'range',
            'totalFindingsOpen',
            'totalFindingsClosed',
            'mtbfData',
            'vibrationInsight'
        ));
    }

    /**
     * Tampilkan halaman Equipment Status — daftar equipment dengan
     * search, filter PT, filter status, dan ringkasan vibrasi/temperatur.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function equipmentStatus(Request $request)
    {
        $filterPt     = $request->get('pt', '');
        $filterStatus = $request->get('status', '');
        $search       = $request->get('search', '');

        $query = CmEquipment::with(['asset.company']);

        // Filter PT
        if ($filterPt) {
            $query->where('pt_location', $filterPt);
        }

        // Filter status — cari equipment dengan last reading sesuai status
        if ($filterStatus) {
            $query->whereHas('readings', function ($q) use ($filterStatus) {
                $q->where('kondisi', $filterStatus)
                  ->whereIn('id', function ($sub) {
                      $sub->selectRaw('MAX(id)')
                          ->from('cm_readings')
                          ->whereColumn('cm_equipment_id', 'cm_equipment.id');
                  });
            });
        }

        // Search — cari tag, PT, plant, atau nama/model asset
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('equipment_tag', 'like', "%{$search}%")
                  ->orWhere('pt_location', 'like', "%{$search}%")
                  ->orWhere('plant', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($sq) use ($search) {
                      $sq->where('description', 'like', "%{$search}%")
                         ->orWhere('model_number', 'like', "%{$search}%");
                  });
            });
        }

        $equipments = $query->orderBy('pt_location')
            ->orderBy('equipment_tag')
            ->paginate(20)
            ->withQueryString();

        // Load reading terakhir untuk ringkasan vibrasi & temperatur
        $equipments->load(['readings' => function ($q) {
            $q->orderBy('tanggal', 'desc');
        }]);

        $equipments->each(function ($eq) {
            $lastReading = $eq->readings->first();
            $eq->current_status     = $lastReading?->kondisi ?? 'good';
            $eq->last_reading_date  = $lastReading?->tanggal;

            // Ringkasan nilai maks vibrasi & temperatur dari reading terakhir
            if ($lastReading) {
                $vibMax = max(
                    $lastReading->ndev_motor ?? 0, $lastReading->ndeh_motor ?? 0, $lastReading->ndea_motor ?? 0,
                    $lastReading->dev_motor ?? 0,  $lastReading->deh_motor ?? 0,  $lastReading->dea_motor ?? 0,
                    $lastReading->ndev_pompa ?? 0, $lastReading->ndeh_pompa ?? 0, $lastReading->ndea_pompa ?? 0,
                    $lastReading->dev_pompa ?? 0,  $lastReading->deh_pompa ?? 0,  $lastReading->dea_pompa ?? 0
                );
                $tempMax = max(
                    $lastReading->temp_de_motor ?? 0,  $lastReading->temp_nde_motor ?? 0,
                    $lastReading->temp_de_pompa ?? 0,  $lastReading->temp_nde_pompa ?? 0
                );
                $eq->last_vibration = $vibMax > 0 ? number_format($vibMax, 1) : '-';
                $eq->last_temp      = $tempMax > 0 ? number_format($tempMax, 1) : '-';
            } else {
                $eq->last_vibration = '-';
                $eq->last_temp      = '-';
            }
        });

        $ptList = CmEquipment::select('pt_location')->distinct()->pluck('pt_location');

        return view('cm.equipment-status', compact(
            'equipments', 'ptList',
            'filterPt', 'filterStatus', 'search'
        ));
    }

    /**
     * Tampilkan halaman Report & Analysis.
     */
    public function reportAnalysis(Request $request)
    {
        $filterTahun = $request->get('tahun', now()->format('Y'));
        $filterPt    = $request->get('pt', '');

        // Statistik umum untuk laporan
        $totalEquipment = CmEquipment::when($filterPt, fn($q) => $q->where('pt_location', $filterPt))->count();

        $totalReadings = CmReading::when($filterPt, function ($q) use ($filterPt) {
            $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt));
        })->count();

        $totalFindings = CmFinding::when($filterPt, function ($q) use ($filterPt) {
            $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt));
        })->count();

        $openFindings = CmFinding::where('status', 'open')
            ->when($filterPt, function ($q) use ($filterPt) {
                $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt));
            })->count();

        $monitoringProgress = CmMonthlyTracking::where('tahun', $filterTahun)
            ->when($filterPt, function ($q) use ($filterPt) {
                $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt));
            })
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $monitoringSudah = (int) ($monitoringProgress['sudah']->total ?? 0);
        $monitoringTotal = $monitoringProgress->sum('total');
        $monitoringPct = $monitoringTotal > 0 ? round(($monitoringSudah / $monitoringTotal) * 100, 1) : 0;

        $ptList = CmEquipment::select('pt_location')->distinct()->pluck('pt_location');
        $tahunList = range(now()->year - 2, now()->year);

        return view('cm.report-analysis', compact(
            'totalEquipment', 'totalReadings', 'totalFindings', 'openFindings',
            'monitoringSudah', 'monitoringTotal', 'monitoringPct',
            'ptList', 'tahunList', 'filterTahun', 'filterPt'
        ));
    }

    /**
     * Ambil data JSON untuk chart trend bulanan.
     */
    public function trendChartData(Request $request)
    {
        $filterPt = $request->get('pt', '');

        $query = CmReading::selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, kondisi, COUNT(*) as total')
            ->groupBy('tahun', 'bulan', 'kondisi')
            ->orderBy('tahun')
            ->orderBy('bulan');

        if ($filterPt) {
            $query->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }

        $data = $query->get();

        // Format untuk chart
        $months = collect();
        $grouped = $data->groupBy(fn($d) => $d->tahun . '-' . str_pad($d->bulan, 2, '0', STR_PAD_LEFT));

        $result = [];
        foreach ($grouped as $period => $items) {
            $row = ['period' => $period];
            $row['good'] = $items->where('kondisi', 'good')->sum('total');
            $row['alarm'] = $items->where('kondisi', 'alarm')->sum('total');
            $row['danger'] = $items->where('kondisi', 'danger')->sum('total');
            $row['visual_bad'] = $items->where('kondisi', 'visual_bad')->sum('total');
            $result[] = $row;
        }

        return response()->json($result);
    }

    /**
     * Ambil data JSON untuk chart donut (breakdown per PT).
     */
    public function donutData(Request $request)
    {
        $filterPt = $request->get('pt', '');

        $query = CmReading::selectRaw('cm_equipment.pt_location, cm_readings.kondisi, COUNT(*) as total')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->groupBy('cm_equipment.pt_location', 'cm_readings.kondisi')
            ->orderBy('cm_equipment.pt_location');

        if ($filterPt) {
            $query->where('cm_equipment.pt_location', $filterPt);
        }

        $data = $query->get()->groupBy('pt_location');

        $result = [];
        foreach ($data as $pt => $items) {
            $result[$pt] = ['good' => 0, 'alarm' => 0, 'danger' => 0, 'visual_bad' => 0];
            foreach ($items as $item) {
                $result[$pt][$item->kondisi] = (int) $item->total;
            }
        }

        return response()->json($result);
    }

    /**
     * Render partial summary cards via AJAX.
     */
    public function overviewSummary(Request $request)
    {
        $filterPt = $request->get('pt', '');

        $query = CmReading::query();
        if ($filterPt) {
            $query->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }

        $totalRecords  = (clone $query)->count();
        $goodCount     = (clone $query)->where('kondisi', 'good')->count();
        $alarmCount    = (clone $query)->where('kondisi', 'alarm')->count();
        $dangerCount   = (clone $query)->where('kondisi', 'danger')->count();
        $visualBadCount = (clone $query)->where('kondisi', 'visual_bad')->count();

        $goodPct     = $totalRecords > 0 ? round(($goodCount / $totalRecords) * 100, 1) : 0;
        $alarmPct    = $totalRecords > 0 ? round(($alarmCount / $totalRecords) * 100, 1) : 0;
        $dangerPct   = $totalRecords > 0 ? round(($dangerCount / $totalRecords) * 100, 1) : 0;
        $visualBadPct = $totalRecords > 0 ? round(($visualBadCount / $totalRecords) * 100, 1) : 0;

        return view('cm._overview-cards', compact(
            'totalRecords', 'goodCount', 'goodPct',
            'alarmCount', 'alarmPct',
            'dangerCount', 'dangerPct',
            'visualBadCount', 'visualBadPct'
        ));
    }

    /**
     * Deteksi trend kenaikan vibrasi motor (NDEV, NDEH, NDEA) dari data
     * chart yang sudah di-sort ascending. Mengembalikan array insight
     * atau null jika tidak ada kenaikan signifikan.
     *
     * @param  \Illuminate\Support\Collection  $chartData  collection of {tanggal, ndev_motor, ndeh_motor, ndea_motor}
     * @return array|null  ['parameter', 'from_val', 'to_val', 'days_span', 'projection_days', 'message']
     */
    private function detectVibrationTrend($chartData): ?array
    {
        $config      = config('cm.vibration_insight');
        $minPoints   = $config['min_data_points'] ?? 3;
        $risePct     = $config['significant_rise_pct'] ?? 20;
        $baselineCnt = $config['baseline_count'] ?? 3;
        $projDays    = $config['projection_days'] ?? 30;
        $alarmThr    = $config['thresholds']['alarm'] ?? 7.1;
        $dangerThr   = $config['thresholds']['danger'] ?? 11.2;

        if (!$chartData || $chartData->count() < $minPoints) {
            return null;
        }

        // Parameter motor yang dipantau untuk insight
        $params = [
            ['key' => 'ndev_motor', 'label' => 'NDEV'],
            ['key' => 'ndeh_motor', 'label' => 'NDEH'],
            ['key' => 'ndea_motor', 'label' => 'NDEA'],
        ];

        $data = $chartData->values();

        foreach ($params as $param) {
            $key = $param['key'];

            // Ambil N titik terakhir yang tidak null
            $recentValues = $data->pluck($key)->filter(function ($v) {
                return $v !== null && $v !== '';
            })->values();

            if ($recentValues->count() < $minPoints) {
                continue;
            }

            // Ambil N titik terakhir
            $lastN = $recentValues->slice(-$minPoints);

            // Hitung slope (linear regression) dari titik terakhir
            $indices = range(0, $lastN->count() - 1);
            $vals    = $lastN->values()->toArray();

            $slope = $this->calculateSlope($indices, $vals);

            // Jika slope negatif atau mendekati nol — stabil/menurun, skip
            if ($slope <= 0.001) {
                continue;
            }

            // Ambil rata-rata baseline (N titik sebelum titik terakhir)
            $baselineValues = $recentValues->slice(-$minPoints - $baselineCnt, $baselineCnt);

            if ($baselineValues->count() < 1) {
                continue;
            }

            $baselineAvg = $baselineValues->avg();
            $latestVal   = $lastN->last();

            // Bandingkan kenaikan persentase terhadap rata-rata baseline
            $riseRatio = $baselineAvg > 0 ? (($latestVal - $baselineAvg) / $baselineAvg) * 100 : 0;

            if ($riseRatio < $risePct) {
                // Cek proyeksi ke batas danger
                $daysToDanger = $slope > 0 ? ($dangerThr - $latestVal) / $slope : PHP_FLOAT_MAX;

                if ($daysToDanger > $projDays) {
                    continue; // Tidak signifikan dan tidak dalam jangka waktu proyeksi
                }
            }

            // Hitung selisih hari antara titik pertama dan terakhir
            $firstDate = $data[$data->count() - $lastN->count()]['tanggal'] ?? null;
            $lastDate  = $data->last()['tanggal'] ?? null;
            $daysSpan  = 0;
            if ($firstDate && $lastDate) {
                $daysSpan = \Carbon\Carbon::parse($lastDate)->diffInDays(\Carbon\Carbon::parse($firstDate));
            }

            // Dari nilai awal ke nilai akhir
            $fromVal = $lastN->first();
            $toVal   = $latestVal;

            // Proyeksi ke batas danger
            $projectedDaysToDanger = $slope > 0 ? ($dangerThr - $latestVal) / $slope : PHP_FLOAT_MAX;
            $projectedDaysToAlarm  = $slope > 0 ? ($alarmThr - $latestVal) / $slope : PHP_FLOAT_MAX;

            $projectionMsg = '';
            $nearestDays   = null;

            if ($projectedDaysToDanger <= $projDays && $projectedDaysToDanger > 0) {
                $nearestDays = ceil($projectedDaysToDanger);
                $projectionMsg = "Proyeksi menyentuh batas Danger dalam ~{$nearestDays} hari.";
            } elseif ($projectedDaysToAlarm <= $projDays && $projectedDaysToAlarm > 0) {
                $nearestDays = ceil($projectedDaysToAlarm);
                $projectionMsg = "Proyeksi menyentuh batas Alarm dalam ~{$nearestDays} hari.";
            }

            return [
                'parameter'    => $param['label'],
                'from_val'     => number_format($fromVal, 2),
                'to_val'       => number_format($toVal, 2),
                'days_span'    => $daysSpan,
                'rise_pct'     => round($riseRatio, 1),
                'slope'        => round($slope, 4),
                'projected_days_to_danger' => $projectedDaysToDanger > 0 && $projectedDaysToDanger < PHP_FLOAT_MAX
                    ? ceil($projectedDaysToDanger) : null,
                'projected_days_to_alarm'  => $projectedDaysToAlarm > 0 && $projectedDaysToAlarm < PHP_FLOAT_MAX
                    ? ceil($projectedDaysToAlarm) : null,
                'projection_message' => $projectionMsg,
                'message'      => "Tren vibrasi {$param['label']} naik signifikan (dari "
                    . number_format($fromVal, 2) . " mm/s ke " . number_format($toVal, 2)
                    . " mm/s dalam {$daysSpan} hari terakhir, kenaikan {$riseRatio}%). "
                    . $projectionMsg,
            ];
        }

        return null;
    }

    /**
     * Hitung slope (koefisien linear) dari serangkaian data (x, y).
     * Menggunakan metode least squares sederhana.
     *
     * @param  array  $x  Index/nilai sumbu X (hari ke-0, 1, 2, ...)
     * @param  array  $y  Nilai sumbu Y (mm/s)
     * @return float
     */
    private function calculateSlope(array $x, array $y): float
    {
        $n = count($x);
        if ($n < 2) {
            return 0;
        }

        $sumX  = array_sum($x);
        $sumY  = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $denom = ($n * $sumX2 - $sumX * $sumX);
        if ($denom == 0) {
            return 0;
        }

        return ($n * $sumXY - $sumX * $sumY) / $denom;
    }

    /**
     * Konversi angka bulan ke label Indonesia.
     */
    private function bulanLabel(int $bulan): string
    {
        $labels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        return $labels[$bulan] ?? '';
    }

    // ====================================================================
    // EXPORT METHODS
    // ====================================================================

    /**
     * Export readings ke Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportReadings(Request $request)
    {
        $filterPt    = $request->get('pt', '');
        $filterTahun = $request->get('tahun', '');
        $filterBulan = $request->get('bulan', '');

        $exporter = new CmReadingsExport();
        return $exporter->export(
            $filterPt ?: null,
            $filterTahun ?: null,
            $filterBulan ?: null
        );
    }

    /**
     * Export findings ke Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportFindings(Request $request)
    {
        $filterPt     = $request->get('pt', '');
        $filterStatus = $request->get('status', '');

        $exporter = new CmFindingsExport();
        return $exporter->export(
            $filterPt ?: null,
            $filterStatus ?: null
        );
    }

    /**
     * Export monitoring monthly tracking ke Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportMonitoring(Request $request)
    {
        $filterTahun = $request->get('tahun', '');
        $filterPt    = $request->get('pt', '');

        $exporter = new CmMonitoringExport();
        return $exporter->export(
            $filterTahun ?: null,
            $filterPt ?: null
        );
    }
}
