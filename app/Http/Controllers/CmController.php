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
use Rap2hpoutre\FastExcel\FastExcel;

class CmController extends Controller
{
    /**
     * Tampilkan halaman Overview Condition Monitoring.
     */
    public function overview(Request $request)
    {
        $filterPt    = $request->input('pt');
        $filterTahun = $request->input('tahun', now()->year);
        $filterBulan = $request->input('bulan');
        $filterStatus = $request->get('status', '');

        // Query dasar dengan filter
        $baseQuery = CmReading::query();
        if ($filterPt) {
            $baseQuery->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }
        if ($filterTahun) {
            $baseQuery->whereYear('tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $baseQuery->whereMonth('tanggal', $filterBulan);
        }
        if ($filterStatus) {
            $baseQuery->where('kondisi', $filterStatus);
        }

        // Data untuk summary cards
        $totalRecords  = (clone $baseQuery)->count();
        $goodCount     = (clone $baseQuery)->where('kondisi', 'good')->count();
        $alarmCount    = (clone $baseQuery)->where('kondisi', 'alarm')->count();
        $dangerCount   = (clone $baseQuery)->where('kondisi', 'danger')->count();
        $visualBadCount = (clone $baseQuery)->where('kondisi', 'visual_bad')->count();

        $goodPct = $totalRecords > 0 ? round(($goodCount / $totalRecords) * 100, 1) : 0;
        $alarmPct = $totalRecords > 0 ? round(($alarmCount / $totalRecords) * 100, 1) : 0;
        $dangerPct = $totalRecords > 0 ? round(($dangerCount / $totalRecords) * 100, 1) : 0;
        $visualBadPct = $totalRecords > 0 ? round(($visualBadCount / $totalRecords) * 100, 1) : 0;

        // Breakdown status per PT untuk donut chart
        $donutQuery = CmReading::selectRaw('cm_equipment.pt_location, cm_readings.kondisi, COUNT(*) as total')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id');

        if ($filterPt) {
            $donutQuery->where('cm_equipment.pt_location', $filterPt);
        }
        if ($filterTahun) {
            $donutQuery->whereYear('cm_readings.tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $donutQuery->whereMonth('cm_readings.tanggal', $filterBulan);
        }
        if ($filterStatus) {
            $donutQuery->where('cm_readings.kondisi', $filterStatus);
        }

        $statusPerPt = $donutQuery
            ->groupBy('cm_equipment.pt_location', 'cm_readings.kondisi')
            ->orderBy('cm_equipment.pt_location')
            ->get()
            ->groupBy('pt_location');

        // Trend bulanan stacked
        $trendQuery = CmReading::selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, kondisi, COUNT(*) as total');

        if ($filterPt) {
            $trendQuery->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }
        if ($filterTahun) {
            $trendQuery->whereYear('tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $trendQuery->whereMonth('tanggal', $filterBulan);
        }
        if ($filterStatus) {
            $trendQuery->where('kondisi', $filterStatus);
        }

        $trendData = $trendQuery
            ->groupBy('tahun', 'bulan', 'kondisi')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        // Top 10 vibrasi tertinggi
        $topVibrasiQuery = CmReading::selectRaw('cm_readings.*, cm_equipment.equipment_tag, cm_equipment.pt_location')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->where('kondisi', 'danger');

        if ($filterPt) {
            $topVibrasiQuery->where('cm_equipment.pt_location', $filterPt);
        }
        if ($filterTahun) {
            $topVibrasiQuery->whereYear('cm_readings.tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $topVibrasiQuery->whereMonth('cm_readings.tanggal', $filterBulan);
        }

        $topVibrasi = $topVibrasiQuery
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
        $filterPt     = $request->input('pt');
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

        $findings = $query->orderBy('tanggal_temuan', 'desc')->paginate(10)->withQueryString();

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
        $filterTahun = $request->input('tahun', now()->year);
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
        $filterPt     = $request->input('pt');
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
     * 
     * Menyediakan data untuk:
     * 1. Section "Equipment Vibrasi Tinggi per PT" — breakdown 3 kolom per PT
     * 2. Section "Ranking Analisa Bulanan" — tabel trend per kategori (semua PT)
     * 3. Insight analisa otomatis rule-based
     * 4. Export Analisa Vibrasi (Excel)
     */
    public function reportAnalysis(Request $request)
    {
        $filterTahun = $request->input('tahun', now()->year);
        $filterPt    = $request->input('pt');
        $filterBulan = $request->input('bulan');

        // Threshold dari config
        $vibThreshold = config('cm.high_vibration.threshold', 4.5);
        $statusFilter = config('cm.high_vibration.status_filter', ['alarm', 'danger']);

        // ---------------------------------------------------------------
        // SECTION 1: Equipment Vibrasi Tinggi per PT (LATEST PER EQUIPMENT)
        // ---------------------------------------------------------------
        // STEP 1: Cari reading TERBARU per equipment (self-join by MAX tanggal).
        // PENTING: pakai MAX(tanggal) karena id TIDAK mencerminkan urutan
        // tanggal (ada anomali — id lebih besar tapi tanggal lebih kecil).
        $latestReadingIds = DB::table('cm_readings as r1')
            ->select('r1.id')
            ->whereRaw('r1.tanggal = (
                select MAX(r2.tanggal) from cm_readings r2
                where r2.cm_equipment_id = r1.cm_equipment_id
            )')
            ->pluck('r1.id');

        // STEP 2: Dari reading TERBARU itu saja, filter status ALARM/DANGER.
        // TIDAK filter analysis dulu — banyak latest reading yang analysis-nya
        // kosong, tapi analysis-nya ADA di reading sebelumnya. Kami ambil
        // analysis dari reading terakhir yang PUNYA analysis sebagai fallback.
        // STEP 2a: Cari ID reading terakhir yang PUNYA analysis per equipment
        // (untuk fallback analysis jika latest reading analysis-nya kosong)
        $latestAnalysisIds = DB::table('cm_readings as r1')
            ->select(DB::raw('MAX(r1.id) as id'))
            ->whereNotNull('r1.analysis')
            ->where('r1.analysis', '!=', '')
            ->groupBy('r1.cm_equipment_id')
            ->pluck('id');

        // STEP 2b: Ambil semua latest reading alarm/danger (tanpa filter analysis)
        $baseLatestRaw = DB::table('cm_readings')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->whereIn('cm_readings.id', $latestReadingIds)
            ->whereIn('cm_readings.kondisi', $statusFilter)
            ->where('cm_readings.max_vibration', '>', $vibThreshold);

        if ($filterTahun) { $baseLatestRaw->whereYear('cm_readings.tanggal', $filterTahun); }
        if ($filterBulan) { $baseLatestRaw->whereMonth('cm_readings.tanggal', $filterBulan); }
        if ($filterPt) { $baseLatestRaw->where('cm_equipment.pt_location', $filterPt); }

        // LEFT JOIN ke analysis fallback
        $latestRows = (clone $baseLatestRaw)
            ->leftJoinSub(
                DB::table('cm_readings')
                    ->select('cm_equipment_id', 'analysis as fallback_analysis')
                    ->whereIn('id', $latestAnalysisIds),
                'fallback',
                'fallback.cm_equipment_id', '=', 'cm_readings.cm_equipment_id'
            )
            ->select(
                'cm_equipment.pt_location',
                DB::raw("COALESCE(NULLIF(cm_readings.analysis, ''), fallback.fallback_analysis) as analysis"),
                'cm_readings.cm_equipment_id',
                'cm_equipment.equipment_tag',
                'cm_readings.kondisi',
                'cm_readings.tanggal'
            )
            ->get()
            ->filter(function ($row) {
                // Hanya equipment yang analysis-nya terisi (dari latest atau fallback)
                return !empty($row->analysis);
            })
            ->values();

        // Daftar PT
        $ptListForCards = $filterPt
            ? [$filterPt]
            : $latestRows->pluck('pt_location')->unique()->sort()->values()->toArray();

        $ptBreakdown = [];

        foreach ($ptListForCards as $pt) {
            $ptRows = $latestRows->where('pt_location', $pt);
            $totalPt = $ptRows->count();

            if ($totalPt === 0) {
                $ptBreakdown[$pt] = ['total' => 0, 'categories' => []];
                continue;
            }

            // Group by analysis — sudah 1 per equipment (pakai Collection)
            $catGroups = $ptRows->groupBy('analysis')->sortByDesc(function ($group) {
                return $group->count();
            });

            $categories = [];
            $rank = 1;
            foreach ($catGroups as $analysisName => $group) {
                $count = $group->count();
                $pct = $totalPt > 0 ? round(($count / $totalPt) * 100, 1) : 0;

                // Sample equipment (max 10, unik per tag)
                $sampleTags = $group->pluck('equipment_tag')->unique()->take(10);
                $equipments = $sampleTags->map(function ($tag) use ($group) {
                    $row = $group->firstWhere('equipment_tag', $tag);
                    $bulanLabel = $row->tanggal
                        ? $this->bulanLabel((int) \Carbon\Carbon::parse($row->tanggal)->format('n')) . '-' . \Carbon\Carbon::parse($row->tanggal)->format('y')
                        : '';
                    return [
                        'tag'    => $tag,
                        'status' => $row->kondisi,
                        'bulan'  => $bulanLabel,
                    ];
                    });

                $categories[] = [
                    'rank'        => $rank++,
                    'name'        => $analysisName,
                    'count'       => $count,
                    'percentage'  => $pct,
                    'equipments'  => $equipments,
                ];
            }

            $ptBreakdown[$pt] = [
                'total'      => $totalPt,
                'categories' => $categories,
            ];
        }

        // ---------------------------------------------------------------
        // SECTION 2: Ranking Analisa Bulanan (tabel trend per kategori)
        // ---------------------------------------------------------------
        // SEMUA histori kejadian (bukan latest-only) — supaya trend bulanan
        // tetap valid untuk melihat pola historis, meskipun equipment sudah
        // membaik di bulan berikutnya.
        $trendAllQuery = CmReading::query()
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->whereIn('cm_readings.kondisi', $statusFilter)
            ->where('cm_readings.max_vibration', '>', $vibThreshold)
            ->whereNotNull('cm_readings.analysis')
            ->where('cm_readings.analysis', '!=', '');

        if ($filterTahun) { $trendAllQuery->whereYear('cm_readings.tanggal', $filterTahun); }
        if ($filterBulan) { $trendAllQuery->whereMonth('cm_readings.tanggal', $filterBulan); }
        if ($filterPt) { $trendAllQuery->where('cm_equipment.pt_location', $filterPt); }

        $trendRaw = (clone $trendAllQuery)
            ->selectRaw('cm_readings.analysis, MONTH(cm_readings.tanggal) as bulan, COUNT(DISTINCT cm_readings.cm_equipment_id) as total')
            ->groupBy('cm_readings.analysis', 'bulan')
            ->orderBy('cm_readings.analysis')
            ->orderBy('bulan')
            ->get();

        // Kumpulkan semua kategori dan bulan
        $allCategories = $trendRaw->pluck('analysis')->unique()->values()->toArray();
        $allMonths = range(1, 12);

        // Bangun tabel ranking
        $rankingTable = [];
        $totalsPerBulan = array_fill_keys($allMonths, 0);
        $grandTotalAll = 0;

        foreach ($allCategories as $cat) {
            $row = ['analysis' => $cat];
            $rowTotal = 0;
            foreach ($allMonths as $m) {
                $val = $trendRaw->firstWhere(function ($item) use ($cat, $m) {
                    return $item->analysis === $cat && (int) $item->bulan === $m;
                });
                $count = $val ? (int) $val->total : 0;
                $row[$m] = $count;
                $rowTotal += $count;
                $totalsPerBulan[$m] += $count;
            }
            $row['total'] = $rowTotal;
            $grandTotalAll += $rowTotal;
            $rankingTable[] = $row;
        }

        // Urutkan berdasarkan total terbesar (ranking)
        usort($rankingTable, fn($a, $b) => $b['total'] <=> $a['total']);

        // Tambahkan rank (setelah sorting)
        $rankedTable = [];
        $rank = 1;
        foreach ($rankingTable as &$row) {
            $row['rank'] = $rank++;
            $rankedTable[] = $row;
        }

        // ---------------------------------------------------------------
        // SECTION 3: Insight Analisa Otomatis (Rule-Based)
        // ---------------------------------------------------------------
        $insight = null;

        if (!empty($rankedTable) && $rankedTable[0]['total'] > 0) {
            $topCategory = $rankedTable[0];

            // Kategori dominan
            $dominantCatName = $topCategory['analysis'];
            $dominantTotal = $topCategory['total'];

            // Cari PT dengan proporsi tertinggi untuk kategori dominan
            $ptProporsi = [];
            foreach ($ptBreakdown as $pt => $ptData) {
                foreach ($ptData['categories'] as $cat) {
                    if ($cat['name'] === $dominantCatName) {
                        $ptProporsi[$pt] = [
                            'count' => $cat['count'],
                            'pct'   => $cat['percentage'],
                            'total' => $ptData['total'],
                        ];
                        break;
                    }
                }
            }

            // PT dengan proporsi tertinggi
            $topPt = null;
            $topPtPct = 0;
            foreach ($ptProporsi as $pt => $prop) {
                if ($prop['pct'] > $topPtPct) {
                    $topPt = $pt;
                    $topPtPct = $prop['pct'];
                }
            }

            // Trend naik/turun — bandingkan 2 bulan terakhir
            $trendDirection = null;
            $monthsWithData = array_filter($allMonths, fn($m) => $topCategory[$m] > 0);
            $sortedMonths = array_values($monthsWithData);
            if (count($sortedMonths) >= 2) {
                $lastMonth = end($sortedMonths);
                $prevMonth = prev($sortedMonths);
                $lastVal = $topCategory[$lastMonth];
                $prevVal = $topCategory[$prevMonth];

                if ($lastVal > $prevVal) {
                    $diff = $lastVal - $prevVal;
                    $trendDirection = "naik {$diff} kasus dari bulan sebelumnya";
                } elseif ($lastVal < $prevVal) {
                    $diff = $prevVal - $lastVal;
                    $trendDirection = "turun {$diff} kasus dari bulan sebelumnya";
                } else {
                    $trendDirection = "stabil (sama dengan bulan sebelumnya)";
                }
            } else {
                $trendDirection = "data baru tersedia untuk 1 bulan";
            }

            $insight = [
                'dominant_category' => $dominantCatName,
                'dominant_total'    => $dominantTotal,
                'top_pt'            => $topPt,
                'top_pt_pct'        => $topPtPct,
                'trend_direction'   => $trendDirection,
            ];
        }

        // ---------------------------------------------------------------
        // DATA UNTUK RINGKASAN UMUM (existing)
        // ---------------------------------------------------------------
        $totalEquipment = CmEquipment::when($filterPt, fn($q) => $q->where('pt_location', $filterPt))->count();

        $totalReadingsQuery = CmReading::when($filterPt, fn($q) => $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt)));
        if ($filterTahun) { $totalReadingsQuery->whereYear('tanggal', $filterTahun); }
        $totalReadings = $totalReadingsQuery->count();

        $totalFindings = CmFinding::when($filterPt, fn($q) => $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt)))->count();

        $openFindings = CmFinding::where('status', 'open')
            ->when($filterPt, fn($q) => $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt)))
            ->count();

        $monitoringProgress = CmMonthlyTracking::where('tahun', $filterTahun)
            ->when($filterPt, fn($q) => $q->whereHas('equipment', fn($sq) => $sq->where('pt_location', $filterPt)))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $monitoringSudah = (int) ($monitoringProgress['sudah']->total ?? 0);
        $monitoringTotal = $monitoringProgress->sum('total');
        $monitoringPct = $monitoringTotal > 0 ? round(($monitoringSudah / $monitoringTotal) * 100, 1) : 0;

        // Daftar bulan untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $ptList = CmEquipment::select('pt_location')->distinct()->pluck('pt_location');
        $tahunList = range(now()->year - 2, now()->year);

        return view('cm.report-analysis', compact(
            'totalEquipment', 'totalReadings', 'totalFindings', 'openFindings',
            'monitoringSudah', 'monitoringTotal', 'monitoringPct',
            'ptList', 'tahunList', 'filterTahun', 'filterPt', 'filterBulan',
            'bulanList',
            'ptBreakdown', 'rankedTable', 'allMonths', 'insight',
        ));
    }

    /**
     * Ambil data JSON untuk chart trend bulanan.
     */
    public function trendChartData(Request $request)
    {
        $filterPt    = $request->input('pt');
        $filterTahun = $request->get('tahun', '');
        $filterBulan = $request->input('bulan');
        $filterStatus = $request->get('status', '');

        $query = CmReading::selectRaw('YEAR(tanggal) as tahun, MONTH(tanggal) as bulan, kondisi, COUNT(*) as total');

        if ($filterPt) {
            $query->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }
        if ($filterTahun) {
            $query->whereYear('tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $query->whereMonth('tanggal', $filterBulan);
        }
        if ($filterStatus) {
            $query->where('kondisi', $filterStatus);
        }

        $data = $query
            ->groupBy('tahun', 'bulan', 'kondisi')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

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
        $filterPt    = $request->input('pt');
        $filterTahun = $request->get('tahun', '');
        $filterBulan = $request->input('bulan');
        $filterStatus = $request->get('status', '');

        $query = CmReading::selectRaw('cm_equipment.pt_location, cm_readings.kondisi, COUNT(*) as total')
            ->join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id');

        if ($filterPt) {
            $query->where('cm_equipment.pt_location', $filterPt);
        }
        if ($filterTahun) {
            $query->whereYear('cm_readings.tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $query->whereMonth('cm_readings.tanggal', $filterBulan);
        }
        if ($filterStatus) {
            $query->where('cm_readings.kondisi', $filterStatus);
        }

        $data = $query
            ->groupBy('cm_equipment.pt_location', 'cm_readings.kondisi')
            ->orderBy('cm_equipment.pt_location')
            ->get()
            ->groupBy('pt_location');

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
        $filterPt    = $request->input('pt');
        $filterTahun = $request->get('tahun', '');
        $filterBulan = $request->input('bulan');
        $filterStatus = $request->get('status', '');

        $query = CmReading::query();
        if ($filterPt) {
            $query->whereHas('equipment', fn($q) => $q->where('pt_location', $filterPt));
        }
        if ($filterTahun) {
            $query->whereYear('tanggal', $filterTahun);
        }
        if ($filterBulan) {
            $query->whereMonth('tanggal', $filterBulan);
        }
        if ($filterStatus) {
            $query->where('kondisi', $filterStatus);
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
        $filterPt    = $request->input('pt');
        $filterTahun = $request->get('tahun', '');
        $filterBulan = $request->input('bulan');
        $filterStatus = $request->get('status', '');

        $exporter = new CmReadingsExport();
        return $exporter->export(
            $filterPt ?: null,
            $filterTahun ?: null,
            $filterBulan ?: null,
            $filterStatus ?: null
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
        $filterPt     = $request->input('pt');
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
        $filterPt    = $request->input('pt');

        $exporter = new CmMonitoringExport();
        return $exporter->export(
            $filterTahun ?: null,
            $filterPt ?: null
        );
    }

    /**
     * Export Analisa Vibrasi ke Excel.
     *
     * Data yang di-export mencakup:
     * 1. Sheet "Per PT" — breakdown equipment vibrasi tinggi per kategori per PT
     * 2. Sheet "Ranking Bulanan" — tabel ranking kategori per bulan (semua PT)
     *
     * Filter tahun, bulan, dan PT diterapkan sama seperti halaman Report & Analysis.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAnalysis(Request $request)
    {
        $filterTahun = $request->input('tahun', now()->year);
        $filterPt    = $request->input('pt');
        $filterBulan = $request->input('bulan');

        $vibThreshold = config('cm.high_vibration.threshold', 4.5);
        $statusFilter = config('cm.high_vibration.status_filter', ['alarm', 'danger']);

        // --- Sheet 1: Breakdown per PT ---
        // Hanya equipment dengan reading terakhir alarm/danger
        $latestReadingSub = CmReading::selectRaw('cm_equipment_id, MAX(tanggal) as max_tanggal')
            ->groupBy('cm_equipment_id');

        $vibEqIds = CmReading::select('cm_readings.cm_equipment_id')
            ->joinSub($latestReadingSub, 'latest', function ($join) {
                $join->on('cm_readings.cm_equipment_id', '=', 'latest.cm_equipment_id')
                     ->on('cm_readings.tanggal', '=', 'latest.max_tanggal');
            })
            ->whereIn('cm_readings.kondisi', $statusFilter)
            ->where('cm_readings.max_vibration', '>', $vibThreshold)
            ->whereNotNull('cm_readings.analysis')
            ->where('cm_readings.analysis', '!=', '');

        if ($filterTahun) { $vibEqIds->whereYear('cm_readings.tanggal', $filterTahun); }
        if ($filterBulan) { $vibEqIds->whereMonth('cm_readings.tanggal', $filterBulan); }
        if ($filterPt) {
            $vibEqIds->whereIn('cm_readings.cm_equipment_id', function ($q) use ($filterPt) {
                $q->select('id')->from('cm_equipment')->where('pt_location', $filterPt);
            });
        }

        $vibEqIds = $vibEqIds->distinct()->pluck('cm_equipment_id');

        $baseQuery = CmReading::join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->whereIn('cm_readings.cm_equipment_id', $vibEqIds)
            ->whereIn('cm_readings.kondisi', $statusFilter)
            ->where('cm_readings.max_vibration', '>', $vibThreshold)
            ->whereNotNull('cm_readings.analysis')
            ->where('cm_readings.analysis', '!=', '');

        $sheet1Data = (clone $baseQuery)
            ->select(
                'cm_equipment.pt_location',
                'cm_equipment.equipment_tag',
                'cm_equipment.plant',
                'cm_readings.analysis',
                'cm_readings.kondisi',
                'cm_readings.max_vibration',
                'cm_readings.max_temp',
                'cm_readings.tanggal'
            )
            ->orderBy('cm_equipment.pt_location')
            ->orderBy('cm_readings.analysis')
            ->orderBy('cm_equipment.equipment_tag')
            ->get()
            ->map(function ($item) {
                return [
                    'PT'             => $item->pt_location,
                    'Plant'          => $item->plant,
                    'Equipment Tag'  => $item->equipment_tag,
                    'Analysis'       => $item->analysis,
                    'Kondisi'        => $item->kondisi,
                    'Max Vibration'  => $item->max_vibration,
                    'Max Temp'       => $item->max_temp,
                    'Tanggal'        => $item->tanggal ? $item->tanggal->format('Y-m-d') : '',
                ];
            });

        // --- Sheet 2: Ranking Bulanan ---
        $trendBase = CmReading::join('cm_equipment', 'cm_equipment.id', '=', 'cm_readings.cm_equipment_id')
            ->whereIn('cm_readings.kondisi', $statusFilter)
            ->where('cm_readings.max_vibration', '>', $vibThreshold)
            ->whereNotNull('cm_readings.analysis')
            ->where('cm_readings.analysis', '!=', '');

        if ($filterTahun) { $trendBase->whereYear('cm_readings.tanggal', $filterTahun); }
        if ($filterBulan) { $trendBase->whereMonth('cm_readings.tanggal', $filterBulan); }

        $trendRaw = (clone $trendBase)
            ->selectRaw('cm_readings.analysis, MONTH(cm_readings.tanggal) as bulan, COUNT(*) as total')
            ->groupBy('cm_readings.analysis', 'bulan')
            ->orderBy('cm_readings.analysis')
            ->orderBy('bulan')
            ->get();

        $allCategories = $trendRaw->pluck('analysis')->unique()->values()->toArray();
        $allMonths = range(1, 12);

        $rankingRows = [];
        foreach ($allCategories as $cat) {
            $row = ['Kategori' => $cat];
            $rowTotal = 0;
            foreach ($allMonths as $m) {
                $val = $trendRaw->firstWhere(function ($item) use ($cat, $m) {
                    return $item->analysis === $cat && (int) $item->bulan === $m;
                });
                $count = $val ? (int) $val->total : 0;
                $label = $this->bulanLabel($m);
                $row[$label] = $count;
                $rowTotal += $count;
            }
            $row['Total'] = $rowTotal;
            $rankingRows[] = $row;
        }

        usort($rankingRows, fn($a, $b) => $b['Total'] <=> $a['Total']);

        $bulanLabels = array_map(fn($m) => $this->bulanLabel($m), $allMonths);

        // Buat data flat untuk FastExcel (2 sheet via separate files approach — we'll combine inline)
        // FastExcel hanya support single sheet, jadi kita gabung jadi 1 sheet dengan separator header
        // Atau lebih baik: gunakan pendekatan file terpisah / manual spreadsheet
        // Alternatif: gunakan Laravel Excel / PhpSpreadsheet langsung

        // Karena FastExcel tidak mendukung multi-sheet dengan mudah,
        // kita buat satu file dengan dua bagian yang dipisah baris kosong.
        // Atau lebih praktis: buat 2 file terpisah dalam satu ZIP.
        // Paling sederhana: export sebagai 1 sheet dengan kolom berbeda.

        // Pendekatan: export 1 sheet detail + 1 sheet rekap dalam file berbeda? Tidak user-friendly.
        // Lebih baik: gunakan PhpSpreadsheet langsung untuk multi-sheet.
        // Tapi constraint: aplikasi pakai FastExcel. Alternatif: buat 2 sheet via PhpSpreadsheet.

        // Gunakan PhpSpreadsheet langsung untuk multi-sheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // --- Sheet 1: Per PT ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Per PT');

        // Header
        $sheet1Headers = ['PT', 'Plant', 'Equipment Tag', 'Analysis', 'Kondisi', 'Max Vibration (mm/s)', 'Max Temp (C)', 'Tanggal'];
        $col = 'A';
        foreach ($sheet1Headers as $header) {
            $sheet1->setCellValue($col . '1', $header);
            $sheet1->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // Data
        $rowNum = 2;
        foreach ($sheet1Data as $row) {
            $sheet1->setCellValue('A' . $rowNum, $row['PT']);
            $sheet1->setCellValue('B' . $rowNum, $row['Plant']);
            $sheet1->setCellValue('C' . $rowNum, $row['Equipment Tag']);
            $sheet1->setCellValue('D' . $rowNum, $row['Analysis']);
            $sheet1->setCellValue('E' . $rowNum, $row['Kondisi']);
            $sheet1->setCellValue('F' . $rowNum, $row['Max Vibration']);
            $sheet1->setCellValue('G' . $rowNum, $row['Max Temp']);
            $sheet1->setCellValue('H' . $rowNum, $row['Tanggal']);
            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $colLetter) {
            $sheet1->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // --- Sheet 2: Ranking Bulanan ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Ranking Bulanan');

        // Header
        $sheet2Headers = ['Rank', 'Kategori'];
        foreach ($bulanLabels as $label) {
            $sheet2Headers[] = $label;
        }
        $sheet2Headers[] = 'Total';

        $col = 'A';
        foreach ($sheet2Headers as $header) {
            $sheet2->setCellValue($col . '1', $header);
            $sheet2->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // Data
        $rowNum = 2;
        $rank = 1;
        $totalsRow = array_fill(0, count($bulanLabels), 0);
        $grandTotal = 0;

        foreach ($rankingRows as $row) {
            $sheet2->setCellValue('A' . $rowNum, $rank);
            $sheet2->setCellValue('B' . $rowNum, $row['Kategori']);

            $colIdx = 0;
            foreach ($bulanLabels as $label) {
                $val = $row[$label] ?? 0;
                $sheet2->setCellValue(chr(67 + $colIdx) . $rowNum, $val);
                $totalsRow[$colIdx] += $val;
                $colIdx++;
            }

            $sheet2->setCellValue(chr(67 + count($bulanLabels)) . $rowNum, $row['Total']);
            $grandTotal += $row['Total'];
            $rank++;
            $rowNum++;
        }

        // Baris Total
        $sheet2->setCellValue('A' . $rowNum, '');
        $sheet2->setCellValue('B' . $rowNum, 'Total');
        $sheet2->getStyle('B' . $rowNum)->getFont()->setBold(true);
        $colIdx = 0;
        foreach ($bulanLabels as $label) {
            $sheet2->setCellValue(chr(67 + $colIdx) . $rowNum, $totalsRow[$colIdx]);
            $sheet2->getStyle(chr(67 + $colIdx) . $rowNum)->getFont()->setBold(true);
            $colIdx++;
        }
        $sheet2->setCellValue(chr(67 + count($bulanLabels)) . $rowNum, $grandTotal);
        $sheet2->getStyle(chr(67 + count($bulanLabels)) . $rowNum)->getFont()->setBold(true);

        // Auto-size
        $maxCol = chr(67 + count($bulanLabels));
        foreach (range('A', $maxCol) as $colLetter) {
            $sheet2->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Output
        $filename = 'analisa-vibrasi-' . now()->format('Ymd-His') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Stream download
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
