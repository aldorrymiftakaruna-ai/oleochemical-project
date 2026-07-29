@extends('layouts.app')

@section('title', 'CM Monitoring — Condition Monitoring')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="{{ route('cm.overview') }}" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">CM Monitoring</span>
@endsection

@section('content')
    @include('cm._tabs')

    {{-- Alert Box: Equipment Belum Diambil Data --}}
    @if($alertEquipments->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div class="flex-1">
                    <h4 class="font-medium text-amber-800 text-sm mb-2">
                        Belum Diambil Data {{ \Carbon\Carbon::create()->month($bulanIni)->format('F') }} - {{ $tahunIni }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($alertEquipments->groupBy('pt_location') as $pt => $items)
                            <div class="bg-white rounded-lg border border-amber-100 p-3">
                                <p class="text-xs font-semibold text-amber-700 mb-1">{{ $pt }}</p>
                                <ul class="space-y-1">
                                    @foreach($items->take(5) as $eq)
                                        <li class="text-xs text-slate-600 flex justify-between">
                                            <a href="{{ route('cm.equipment-show', $eq->equipment_tag) }}"
                                               class="font-mono text-teal-600 hover:text-teal-800 hover:underline transition-colors">
                                                {{ $eq->equipment_tag }}
                                            </a>
                                            <span class="text-slate-400">{{ $eq->last_month_label }}</span>
                                        </li>
                                    @endforeach
                                    @if($items->count() > 5)
                                        <li class="text-xs text-slate-400">...dan {{ $items->count() - 5 }} lainnya</li>
                                    @endif
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Tahun</label>
                <select name="tahun" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @foreach($tahunList as $th)
                        <option value="{{ $th }}" {{ $filterTahun == $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2 pt-5">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="hide_done" value="1" {{ $hideDone ? 'checked' : '' }}
                           class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-sm text-slate-700">Sembunyikan 100% Done</span>
                </label>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                Terapkan
            </button>
            <a href="{{ route('cm.monitoring') }}" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                Reset
            </a>
            <a href="{{ route('cm.export-monitoring', ['tahun' => $filterTahun]) }}"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
        </form>
    </div>

    {{-- Tabel Monitoring Bulanan --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3 sticky left-0 bg-slate-50 min-w-[180px]">
                            Equipment
                        </th>
                        <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3">PT</th>
                        @foreach(range(1, 12) as $b)
                            <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-2 py-3 min-w-[36px]">
                                {{ Carbon\Carbon::create()->month($b)->format('M') }}
                            </th>
                        @endforeach
                        <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-4 py-3 min-w-[80px]">Done %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($equipments as $eq)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-sm font-medium sticky left-0 bg-white">
                                <a href="{{ route('cm.equipment-show', $eq->equipment_tag) }}"
                                   class="text-teal-700 hover:text-teal-900 hover:underline transition-colors">
                                    {{ $eq->equipment_tag }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $eq->pt_location }}</td>
                            @foreach(range(1, 12) as $b)
                                @php
                                    $track = $eq->monthlyTrackings->firstWhere('bulan', $b);
                                    $isDone = $track && $track->status === 'sudah';
                                @endphp
                                <td class="text-center px-2 py-3">
                                    @if($isDone)
                                        <span class="text-green-500" title="Sudah">&#10003;</span>
                                    @elseif($track && $b <= $bulanIni)
                                        <span class="text-slate-300" title="Belum">&mdash;</span>
                                    @else
                                        <span class="text-slate-200">&middot;</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center px-4 py-3">
                                @php
                                    $pct = $eq->progress_pct;
                                    $color = $pct >= 80 ? 'text-green-600' : ($pct >= 50 ? 'text-amber-600' : 'text-red-500');
                                @endphp
                                <span class="font-semibold {{ $color }}">{{ $pct }}%</span>
                                <div class="w-full bg-slate-100 rounded-full h-1 mt-1 max-w-[60px] mx-auto">
                                    <div class="h-1 rounded-full {{ $pct >= 80 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="px-5 py-8 text-center text-slate-400 text-sm">
                                Tidak ada data monitoring.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
