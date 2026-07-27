
@extends('layouts.app')

@section('title', 'Report & Analysis — Condition Monitoring')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="{{ route('cm.overview') }}" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">Report & Analysis</span>
@endsection

@section('content')
    @include('cm._tabs')

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
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">PT</label>
                <select name="pt" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua PT</option>
                    @foreach($ptList as $pt)
                        <option value="{{ $pt }}" {{ $filterPt === $pt ? 'selected' : '' }}>{{ $pt }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                Terapkan
            </button>
            <a href="{{ route('cm.report-analysis') }}" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Equipment</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalEquipment) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Readings</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totalReadings) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Findings</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($totalFindings) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-slate-400">Finding Open</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($openFindings) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-slate-400">Monitoring {{ $filterTahun }}</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $monitoringPct }}%</p>
            <p class="text-xs text-slate-400">{{ $monitoringSudah }}/{{ $monitoringTotal }} bulan</p>
        </div>
    </div>

    {{-- Laporan Ringkasan --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="font-medium text-slate-900 mb-4">Ringkasan Periodik</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-slate-700 mb-2">Distribusi Monitoring ({{ $filterTahun }})</h4>
                <div class="space-y-2">
                    <div>
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Sudah Diambil</span>
                            <span>{{ $monitoringSudah }} bulan ({{ $monitoringPct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $monitoringPct }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Belum Diambil</span>
                            <span>{{ $monitoringTotal - $monitoringSudah }} bulan ({{ 100 - $monitoringPct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ 100 - $monitoringPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-slate-700 mb-2">Status Equipment Overview</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Total Equipment Terpantau</span>
                        <span class="font-semibold">{{ number_format($totalEquipment) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Reading Tersedia</span>
                        <span class="font-semibold">{{ number_format($totalReadings) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Finding Masih Open</span>
                        <span class="font-semibold text-red-600">{{ number_format($openFindings) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Finding Sudah Closed</span>
                        <span class="font-semibold text-green-600">{{ number_format($totalFindings - $openFindings) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Export Laporan --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-medium text-slate-900 mb-4">Export Laporan Rekap</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('cm.export-readings', ['pt' => $filterPt, 'tahun' => $filterTahun]) }}"
               class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl hover:border-teal-300 hover:bg-teal-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-900 group-hover:text-teal-700 transition-colors">Export Readings</p>
                    <p class="text-xs text-slate-500">Data vibrasi & temperatur</p>
                </div>
            </a>
            <a href="{{ route('cm.export-findings', ['pt' => $filterPt]) }}"
               class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl hover:border-teal-300 hover:bg-teal-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-900 group-hover:text-teal-700 transition-colors">Export Findings</p>
                    <p class="text-xs text-slate-500">Data temuan & severity</p>
                </div>
            </a>
            <a href="{{ route('cm.export-monitoring', ['tahun' => $filterTahun, 'pt' => $filterPt]) }}"
               class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl hover:border-teal-300 hover:bg-teal-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-900 group-hover:text-teal-700 transition-colors">Export Monitoring</p>
                    <p class="text-xs text-slate-500">Data tracking bulanan</p>
                </div>
            </a>
        </div>
    </div>
@endsection
