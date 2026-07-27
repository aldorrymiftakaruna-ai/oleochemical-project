@extends("layouts.app")

@section("page-title", $equipment->equipment_tag . " — Detail Equipment")
@section("page-sub", "Informasi detail & riwayat pembacaan equipment")

@section("content")

<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-900">Filter Rentang Waktu</h3>
        <div class="flex items-center gap-2 flex-wrap">
<a href="?range=7" class="px-4 py-2 text-xs font-medium rounded-lg border transition-colors {{ $range === "7" ? "bg-teal-50 text-teal-700 border-teal-300" : "bg-white text-slate-600 border-slate-300 hover:bg-slate-50" }}">7 Hari</a>
<a href="?range=30" class="px-4 py-2 text-xs font-medium rounded-lg border transition-colors {{ $range === "30" ? "bg-teal-50 text-teal-700 border-teal-300" : "bg-white text-slate-600 border-slate-300 hover:bg-slate-50" }}">30 Hari</a>
<a href="?range=90" class="px-4 py-2 text-xs font-medium rounded-lg border transition-colors {{ $range === "90" ? "bg-teal-50 text-teal-700 border-teal-300" : "bg-white text-slate-600 border-slate-300 hover:bg-slate-50" }}">90 Hari</a>
<a href="?range=365" class="px-4 py-2 text-xs font-medium rounded-lg border transition-colors {{ $range === "365" ? "bg-teal-50 text-teal-700 border-teal-300" : "bg-white text-slate-600 border-slate-300 hover:bg-slate-50" }}">1 Tahun</a>
<a href="?range=all" class="px-4 py-2 text-xs font-medium rounded-lg border transition-colors {{ $range === "all" ? "bg-teal-50 text-teal-700 border-teal-300" : "bg-white text-slate-600 border-slate-300 hover:bg-slate-50" }}">Semua</a>
        </div>
    </div>
</div>
<div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-2xl font-bold font-mono" style="color: #0E9E8E;">{{ $equipment->equipment_tag }}</h2>
                @php
                    $sc = ["good" => "bg-green-100 text-green-700 border-green-300", "alarm" => "bg-amber-100 text-amber-700 border-amber-300", "danger" => "bg-red-100 text-red-700 border-red-300", "visual_bad" => "bg-purple-100 text-purple-700 border-purple-300"];
                    $sdc = ["good" => "bg-green-500", "alarm" => "bg-amber-500", "danger" => "bg-red-500", "visual_bad" => "bg-purple-500"];
                    $cs = $equipment->current_status;
                    $stColor = $sc[$cs] ?? "bg-slate-100 text-slate-600";
                    $dotColor = $sdc[$cs] ?? "bg-slate-500";
                    $stLabel = ucfirst(str_replace("_", " ", $cs));
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $stColor }}">
                    <span class="w-2 h-2 rounded-full mr-1.5 {{ $dotColor }}"></span>
                    {{ $stLabel }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                {{ $equipment->pt_location }}
                @if($equipment->plant)
                    &middot; {{ $equipment->plant }}
                @endif
            </p>
            <p class="text-sm text-slate-700 mt-1">
                {{ $equipment->asset?->description ?? 'Tidak ada data asset' }}
            </p>
        </div>
        <div class="shrink-0">
            <a href="{{ route('cm.equipment-status') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Equipment Status
            </a>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-5 border-t border-slate-100">
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Tipe Lubrikasi</p>
            <p class="text-sm font-medium text-slate-800 mt-0.5">{{ $equipment->tipe_lubrikasi ?? "—" }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">PT / Plant</p>
            <p class="text-sm font-medium text-slate-800 mt-0.5">{{ $equipment->pt_location }} / {{ $equipment->plant ?? "—" }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Total Readings</p>
            <p class="text-sm font-medium text-slate-800 mt-0.5">{{ $equipment->readings()->count() }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide">Total Findings</p>
            <p class="text-sm font-medium text-slate-800 mt-0.5">{{ $totalFindingsOpen + $totalFindingsClosed }} <span class="text-xs text-slate-400 font-normal">({{ $totalFindingsOpen }} open / {{ $totalFindingsClosed }} closed)</span></p>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">Informasi Spek</h3>
        <div class="space-y-3">
            <div>
                <p class="text-xs text-slate-400">Company</p>
                <p class="text-sm text-slate-800 font-medium">{{ $equipment->asset?->company?->name ?? $equipment->asset?->company?->code ?? "—" }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Manufacturer</p>
                <p class="text-sm text-slate-800">{{ $equipment->asset?->manufacturer ?? "—" }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Model Number</p>
                <p class="text-sm text-slate-800">{{ $equipment->asset?->model_number ?? "—" }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Construct Year</p>
                <p class="text-sm text-slate-800">{{ $equipment->asset?->construct_year ?? "—" }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Equipment No</p>
                <p class="text-sm font-mono text-slate-800">{{ $equipment->asset?->equipment_no ?? "—" }}</p>
            </div>
        </div>
    </div>
    <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Total Readings</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $equipment->readings()->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Total Findings</p>
            <div class="mt-1">
                <p class="text-2xl font-bold text-slate-900">{{ $totalFindingsOpen + $totalFindingsClosed }}</p>
                <p class="text-xs mt-0.5"><span class="text-amber-600 font-medium">{{ $totalFindingsOpen }} open</span><span class="text-slate-300 mx-1">/</span><span class="text-green-600 font-medium">{{ $totalFindingsClosed }} closed</span></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Last Reading</p>
            <div class="mt-1">
                <p class="text-sm font-semibold text-slate-800">{{ $lastReading ? \Carbon\Carbon::parse($lastReading->tanggal)->format("d M Y") : "—" }}</p>
                @if($lastReading)
                    <span class="inline-flex items-center gap-1 text-xs font-medium mt-0.5 {{ $lastReading->kondisi === "good" ? "text-green-600" : ($lastReading->kondisi === "alarm" ? "text-amber-600" : ($lastReading->kondisi === "danger" ? "text-red-600" : "text-purple-600")) }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $lastReading->kondisi === "good" ? "bg-green-500" : ($lastReading->kondisi === "alarm" ? "bg-amber-500" : ($lastReading->kondisi === "danger" ? "bg-red-500" : "bg-purple-500")) }}"></span>
                        {{ ucfirst(str_replace("_", " ", $lastReading->kondisi)) }}
                    </span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Vibrasi Maks</p>
            <p class="text-2xl font-bold {{ $vibMax !== "-" && (float) $vibMax > 10 ? "text-red-600" : "text-slate-900" }} mt-1">{{ $vibMax }} <span class="text-sm font-normal text-slate-400">mm/s</span></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Temp Maks</p>
            <p class="text-2xl font-bold {{ $tempMax !== "-" && (float) $tempMax > 80 ? "text-red-600" : "text-slate-900" }} mt-1">{{ $tempMax }} <span class="text-sm font-normal text-slate-400">&deg;C</span></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
            <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Ampere</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $lastReading?->ampere ? number_format($lastReading->ampere, 1) : "—" }} <span class="text-sm font-normal text-slate-400">A</span></p>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">MTBF</p>
        <p class="text-2xl font-bold mt-1" style="color: #0E9E8E;">@if($mtbfData["mtbf"] !== "-"){{ number_format($mtbfData["mtbf"], 1) }} <span class="text-sm font-normal text-slate-400">hari</span>@else-@endif</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Total Failures</p>
        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $mtbfData["total_failures"] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Last Failure</p>
        <div class="mt-1">@if($mtbfData["last_failure"])<p class="text-sm font-semibold text-slate-800">{{ $mtbfData["last_failure"] }}</p><p class="text-xs text-slate-500 mt-0.5">({{ $mtbfData["days_since"] }} hari lalu)</p>@else<p class="text-sm font-semibold text-slate-800">—</p>@endif</div>
    </div>
</div>
{{-- Trending Vibrasi Motor --}}
@if(count($chartData) > 0)
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-900">Trending Vibrasi Motor</h3>
        <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full" style="background: #0E9E8E"></span> NDEV</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full" style="background: #F59E0B"></span> NDEH</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full" style="background: #EF4444"></span> NDEA</span>
        </div>
    </div>
    <div class="relative" style="height: 300px;">
        <canvas id="vibrationChart"></canvas>
    </div>
</div>
@endif
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Riwayat Pembacaan</h3>
        <span class="text-xs text-slate-400">10 entri per halaman</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Tanggal</th>
                    <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Kondisi</th>
                    <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Motor Vib</th>
                    <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Pompa Vib</th>
                    <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Temp</th>
                    <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Ampere</th>
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Remark</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($readings as $r)
                    @php
                        $mv = collect([$r->ndev_motor, $r->ndeh_motor, $r->ndea_motor, $r->dev_motor, $r->deh_motor, $r->dea_motor])->filter(fn($v) => $v !== null)->values();
                        $mvStr = $mv->isNotEmpty() ? $mv->take(3)->map(fn($v) => number_format($v, 1))->implode(" / ") : "—";
                        $pv = collect([$r->ndev_pompa, $r->ndeh_pompa, $r->ndea_pompa, $r->dev_pompa, $r->deh_pompa, $r->dea_pompa])->filter(fn($v) => $v !== null)->values();
                        $pvStr = $pv->isNotEmpty() ? $pv->take(3)->map(fn($v) => number_format($v, 1))->implode(" / ") : "—";
                        $temps = collect([$r->temp_de_motor, $r->temp_nde_motor, $r->temp_de_pompa, $r->temp_nde_pompa])->filter(fn($t) => $t !== null)->values();
                        $tempStr = $temps->isNotEmpty() ? $temps->max() . "&deg;C" : "—";
                        $rs = $r->kondisi;
                        $bc = ["good" => "bg-green-100 text-green-700", "alarm" => "bg-amber-100 text-amber-700", "danger" => "bg-red-100 text-red-700", "visual_bad" => "bg-purple-100 text-purple-700"];
                        $bcColor = $bc[$rs] ?? "bg-slate-100 text-slate-600";
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->tanggal)->format("d M Y") }}</td>
                        <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full {{ $bcColor }}">{{ ucfirst(str_replace("_", " ", $rs)) }}</span></td>
                        <td class="px-4 py-3 text-center font-mono text-xs text-slate-700">{{ $mvStr }}</td>
                        <td class="px-4 py-3 text-center font-mono text-xs text-slate-700">{{ $pvStr }}</td>
                        <td class="px-4 py-3 text-center font-mono text-xs text-slate-700">{!! $tempStr !!}</td>
                        <td class="px-4 py-3 text-right font-mono text-xs text-slate-700">{{ $r->ampere ? number_format($r->ampere, 1) : "—" }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 max-w-[200px] truncate" title="{{ $r->remark ?? "" }}">{{ $r->remark ?? "—" }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada data pembacaan untuk equipment ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($readings->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $readings->appends(["range" => $range])->links() }}</div>
    @endif
</div>
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-900">Temuan (Findings)</h3>
        <span class="text-xs text-slate-400">{{ $equipment->findings->count() }} total</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Kode Finding</th>
                    <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Severity</th>
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Kategori</th>
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Deskripsi</th>
                    <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Status</th>
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">PIC</th>
                    <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Tgl Temuan</th>
                    <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Hari Open</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($equipment->findings->sortByDesc('tanggal_temuan') as $f)
                    @php
                        $sevColors = ["low" => "bg-blue-100 text-blue-700", "medium" => "bg-amber-100 text-amber-700", "high" => "bg-red-100 text-red-700"];
                        $sevColor = $sevColors[$f->severity] ?? "bg-slate-100 text-slate-600";
                        $isOpen = $f->status === "open";
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3"><span class="font-mono text-xs text-teal-700 font-medium">{{ $f->kode_finding }}</span></td>
                        <td class="px-4 py-3 text-center"><span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full {{ $sevColor }}">{{ ucfirst($f->severity) }}</span></td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $f->kategori ?? "—" }}</td>
                        <td class="px-4 py-3 text-xs text-slate-700 max-w-[250px] truncate" title="{{ $f->deskripsi }}">{{ $f->deskripsi ?? "—" }}</td>
                        <td class="px-4 py-3 text-center">@if($isOpen)<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-700"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>Open</span>@else<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>Closed</span>@endif</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $f->pic ?? "—" }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $f->tanggal_temuan ? \Carbon\Carbon::parse($f->tanggal_temuan)->format("d M Y") : "—" }}</td>
                        <td class="px-4 py-3 text-right text-xs {{ $isOpen && $f->hari_open > 7 ? "text-red-600 font-semibold" : "text-slate-600" }}">{{ $f->hari_open ?? "—" }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-slate-400 text-sm">Belum ada temuan untuk equipment ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var canvas = document.getElementById('vibrationChart');
    if (!canvas) return;

    var chartData = @json($chartData);
    if (!chartData || chartData.length === 0) return;

    var labels = chartData.map(function(d) { return d.tanggal; });
    var ndev = chartData.map(function(d) { return d.ndev_motor; });
    var ndeh = chartData.map(function(d) { return d.ndeh_motor; });
    var ndea = chartData.map(function(d) { return d.ndea_motor; });

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'NDEV Motor', data: ndev, borderColor: '#0E9E8E', backgroundColor: 'rgba(14,158,142,0.1)', borderWidth: 2, pointRadius: 3, pointHoverRadius: 5, tension: 0.3, fill: false },
                { label: 'NDEH Motor', data: ndeh, borderColor: '#F59E0B', backgroundColor: 'rgba(245,158,11,0.1)', borderWidth: 2, pointRadius: 3, pointHoverRadius: 5, tension: 0.3, fill: false },
                { label: 'NDEA Motor', data: ndea, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.1)', borderWidth: 2, pointRadius: 3, pointHoverRadius: 5, tension: 0.3, fill: false }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1E293B', titleFont: { size: 12 }, bodyFont: { size: 12 }, padding: 10, cornerRadius: 8 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94A3B8', maxRotation: 45, maxTicksLimit: 12 } },
                y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { font: { size: 10 }, color: '#94A3B8' }, title: { display: true, text: 'mm/s', color: '#94A3B8', font: { size: 11 } } }
            }
        }
    });
})();
</script>
@endpush