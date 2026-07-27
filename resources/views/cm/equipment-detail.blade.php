@extends('layouts.app')

@section('title', 'Equipment Detail — Condition Monitoring')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="{{ route('cm.overview') }}" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">Equipment Detail</span>
@endsection

@section('content')
    @include('cm._tabs')

    {{-- Pilih Equipment --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1 flex-1 min-w-[250px]">
                <label class="text-xs font-medium text-slate-500">Pilih Equipment</label>
                <select name="id" onchange="this.form.submit()"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">-- Pilih Equipment --</option>
                    @foreach($equipments as $eq)
                        <option value="{{ $eq->id }}" {{ $equipment && $equipment->id === $eq->id ? 'selected' : '' }}>
                            {{ $eq->equipment_tag }} — {{ $eq->pt_location }} ({{ $eq->plant }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($equipment)
        {{-- Info Equipment --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs text-slate-400">Equipment Tag</p>
                <p class="text-sm font-semibold text-slate-900 font-mono mt-0.5">{{ $equipment->equipment_tag }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs text-slate-400">PT / Plant</p>
                <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $equipment->pt_location }} / {{ $equipment->plant }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs text-slate-400">Tipe Lubrikasi</p>
                <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $equipment->tipe_lubrikasi ?? '-' }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs text-slate-400">Total Readings</p>
                <p class="text-sm font-semibold text-slate-900 mt-0.5">{{ $readings->count() }}</p>
            </div>
        </div>

        {{-- Trend Chart --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
            <h3 class="font-medium text-slate-900 mb-4">Trend Parameter Historis</h3>
            <canvas id="detailChart" height="300"></canvas>
        </div>

        {{-- Tabel Readings dengan toggle --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-medium text-slate-900">Riwayat Readings</h3>
                <button id="toggleReadingsBtn"
                        class="px-3 py-1.5 text-xs font-medium text-teal-600 hover:text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg transition-colors">
                    Sembunyikan
                </button>
            </div>
            <div id="readingsTableWrapper" class="overflow-x-auto max-h-[400px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-50">
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Tanggal</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">NDEV M</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">NDEH M</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">NDEA M</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">Temp M</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">NDEV P</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">NDEH P</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">NDEA P</th>
                            <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-3 py-3">Temp P</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($readings as $r)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2.5 text-xs text-slate-600 whitespace-nowrap">{{ $r->tanggal->format('d M Y') }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->ndev_motor }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->ndeh_motor }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->ndea_motor }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->temp_de_motor }}°</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->ndev_pompa }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->ndeh_pompa }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->ndea_pompa }}</td>
                                <td class="px-3 py-2.5 text-right font-mono text-xs">{{ $r->temp_de_pompa }}°</td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $badgeColor = match($r->kondisi) {
                                            'good' => 'bg-green-100 text-green-700',
                                            'alarm' => 'bg-amber-100 text-amber-700',
                                            'danger' => 'bg-red-100 text-red-700',
                                            'visual_bad' => 'bg-purple-100 text-purple-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $r->kondisi)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-8 text-center text-slate-400 text-sm">
                                    Tidak ada data readings.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toggleBtn = document.getElementById('toggleReadingsBtn');
            var wrapper = document.getElementById('readingsTableWrapper');
            if (toggleBtn && wrapper) {
                toggleBtn.addEventListener('click', function() {
                    var isHidden = wrapper.style.display === 'none';
                    wrapper.style.display = isHidden ? '' : 'none';
                    toggleBtn.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
                });
            }
        });
        </script>

        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('detailChart');
            if (!canvas) return;

            const readings = @json($readings);
            if (!readings || !readings.length) return;

            const ctx = canvas.getContext('2d');
            canvas.width = canvas.parentElement.clientWidth;
            canvas.height = 300;

            const padding = {top: 30, right: 30, bottom: 50, left: 60};
            const chartW = canvas.width - padding.left - padding.right;
            const chartH = canvas.height - padding.top - padding.bottom;

            const params = [
                {key: 'ndev_motor', color: '#0E9E8E', label: 'NDEV Motor'},
                {key: 'ndeh_motor', color: '#F59E0B', label: 'NDEH Motor'},
                {key: 'ndea_motor', color: '#8B5CF6', label: 'NDEA Motor'},
                {key: 'temp_de_motor', color: '#EF4444', label: 'Temp Motor'},
            ];

            // Cari max value
            let maxVal = 0;
            readings.forEach(r => {
                params.forEach(p => {
                    const v = parseFloat(r[p.key]) || 0;
                    if (v > maxVal) maxVal = v;
                });
            });
            maxVal = Math.ceil(maxVal * 1.2) || 10;

            const count = readings.length;
            const stepX = count > 1 ? chartW / (count - 1) : chartW / 2;

            // Grid
            ctx.strokeStyle = '#E2E8F0';
            ctx.lineWidth = 0.5;
            for (let i = 0; i <= 5; i++) {
                const y = padding.top + chartH - (chartH * i / 5);
                ctx.beginPath();
                ctx.moveTo(padding.left, y);
                ctx.lineTo(canvas.width - padding.right, y);
                ctx.stroke();

                ctx.fillStyle = '#94A3B8';
                ctx.font = '10px sans-serif';
                ctx.textAlign = 'right';
                ctx.fillText((maxVal * i / 5).toFixed(1), padding.left - 5, y + 3);
            }

            // Y label
            ctx.save();
            ctx.translate(14, padding.top + chartH/2);
            ctx.rotate(-Math.PI/2);
            ctx.fillStyle = '#94A3B8';
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Nilai', 0, 0);
            ctx.restore();

            // Lines
            params.forEach(p => {
                ctx.strokeStyle = p.color;
                ctx.lineWidth = 2;
                ctx.beginPath();

                readings.forEach((r, idx) => {
                    const x = padding.left + idx * stepX;
                    const val = parseFloat(r[p.key]) || 0;
                    const y = padding.top + chartH - (val / maxVal) * chartH;

                    if (idx === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                });
                ctx.stroke();

                // Dots
                readings.forEach((r, idx) => {
                    const x = padding.left + idx * stepX;
                    const val = parseFloat(r[p.key]) || 0;
                    const y = padding.top + chartH - (val / maxVal) * chartH;
                    ctx.fillStyle = p.color;
                    ctx.beginPath();
                    ctx.arc(x, y, 3, 0, Math.PI * 2);
                    ctx.fill();
                });
            });

            // X labels
            const labelStep = Math.max(1, Math.floor(count / 8));
            readings.forEach((r, idx) => {
                if (idx % labelStep !== 0 && idx !== count - 1) return;
                const x = padding.left + idx * stepX;
                ctx.fillStyle = '#64748B';
                ctx.font = '9px sans-serif';
                ctx.textAlign = 'center';
                const label = r.tanggal ? r.tanggal.substring(0, 7) : '';
                ctx.fillText(label, x, canvas.height - padding.bottom + 15);
            });

            // Legend
            let lx = padding.left + 10;
            const ly = 14;
            params.forEach(p => {
                ctx.fillStyle = p.color;
                ctx.fillRect(lx, ly - 5, 12, 3);
                ctx.fillStyle = '#64748B';
                ctx.font = '10px sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(p.label, lx + 16, ly + 1);
                lx += ctx.measureText(p.label + '  ').width + 30;
            });
        });
        </script>
        @endpush

    @else
        <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-sm text-slate-400">
            Silakan pilih equipment untuk melihat detail.
        </div>
    @endif
@endsection
