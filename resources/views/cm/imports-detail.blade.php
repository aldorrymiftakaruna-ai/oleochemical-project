@extends('layouts.app')

@section('title', 'Detail Import — ' . $importLog->nama_file)
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="{{ route('cm.overview') }}" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <a href="{{ route('cm.imports.history') }}" class="hover:text-slate-700">Riwayat Import</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">Detail</span>
@endsection

@section('content')
    {{-- Card Header --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 mb-1">{{ $importLog->nama_file }}</h2>
                <div class="flex items-center gap-4 text-sm text-slate-500">
                    <span>Diupload {{ $importLog->created_at->format('d M Y H:i:s') }}</span>
                    <span class="text-slate-300">|</span>
                    <span>Oleh: {{ $importLog->uploader?->name ?? 'System' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusClasses = [
                        'completed' => 'bg-green-100 text-green-700',
                        'failed'    => 'bg-red-100 text-red-700',
                        'processing' => 'bg-amber-100 text-amber-700',
                        'pending'   => 'bg-slate-100 text-slate-500',
                        'undone'    => 'bg-purple-100 text-purple-600',
                    ];
                    $class = $statusClasses[$importLog->status] ?? 'bg-slate-100 text-slate-500';
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $class }}">
                    {{ ucfirst($importLog->status) }}
                </span>
                @if ($importLog->completed_at)
                    <span class="text-sm text-slate-400">
                        Durasi: {{ $importLog->completed_at->diffForHumans($importLog->created_at, ['short' => true]) }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Breakdown Angka --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="font-medium text-slate-900 mb-4">Breakdown Hasil Import</h3>

        @if (!$balanceOk)
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <strong>Peringatan:</strong> Total tidak balance!
                    Dibaca: {{ number_format($importLog->total_dibaca > 0 ? $importLog->total_dibaca : $importLog->total_baris) }},
                    Terproses: {{ number_format($totalBreakdown) }},
                    Selisih: {{ number_format(($importLog->total_dibaca > 0 ? $importLog->total_dibaca : $importLog->total_baris) - $totalBreakdown) }}
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <div class="p-4 bg-slate-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-slate-900">{{ number_format($importLog->total_dibaca > 0 ? $importLog->total_dibaca : $importLog->total_baris) }}</p>
                <p class="text-xs text-slate-500 mt-1">Total Dibaca</p>
            </div>
            <div class="p-4 bg-teal-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-teal-600">{{ number_format($importLog->insert_baru) }}</p>
                <p class="text-xs text-teal-600 mt-1">Insert</p>
            </div>
            <div class="p-4 bg-amber-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-amber-600">{{ number_format($importLog->update_existing) }}</p>
                <p class="text-xs text-amber-600 mt-1">Update</p>
            </div>
            <div class="p-4 bg-red-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-red-600">{{ number_format($importLog->gagal) }}</p>
                <p class="text-xs text-red-600 mt-1">Gagal</p>
            </div>
            <div class="p-4 bg-orange-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-orange-600">{{ number_format($importLog->total_skipped_duplikat ?? 0) }}</p>
                <p class="text-xs text-orange-600 mt-1">Skip Duplikat</p>
            </div>
            <div class="p-4 bg-orange-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-orange-600">{{ number_format($importLog->total_skipped_kosong ?? 0) }}</p>
                <p class="text-xs text-orange-600 mt-1">Skip Kosong</p>
            </div>
            <div class="p-4 bg-slate-50 rounded-lg text-center">
                <p class="text-2xl font-bold text-slate-600">{{ number_format($importLog->unregistered) }}</p>
                <p class="text-xs text-slate-500 mt-1">Unregistered</p>
            </div>
        </div>

        @if ($balanceOk)
            <div class="mt-4 p-2 bg-green-50 border border-green-200 rounded-lg text-xs text-green-700 text-center">
                Balance OK: {{ number_format($importLog->total_dibaca > 0 ? $importLog->total_dibaca : $importLog->total_baris) }} = {{ number_format($totalBreakdown) }}
            </div>
        @endif
    </div>

    {{-- Equipment Baru yang Terdaftar --}}
    @if ($newEquipments->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-medium text-slate-900">Equipment Baru yang Terdaftar</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $newEquipments->count() }} equipment baru dari import ini</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Equipment Tag</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">PT</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Plant</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Tipe Lubrikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($newEquipments as $eq)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3">
                                    <a href="{{ route('cm.equipment-show', $eq->equipment_tag) }}"
                                       class="font-mono text-sm font-medium text-teal-700 hover:text-teal-900 hover:underline transition-colors">
                                        {{ $eq->equipment_tag }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $eq->pt_location }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $eq->plant }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $eq->tipe_lubrikasi ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Detail Error & Skip --}}
    @if ($detailItems->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-medium text-slate-900">Detail Error & Skip</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $detailItems->count() }} item</p>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-50">
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Baris</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Sheet Asal</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Kategori</th>
                            <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($detailItems as $item)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-2.5 text-xs font-mono text-slate-500">{{ $item['baris'] }}</td>
                                <td class="px-5 py-2.5 text-xs text-slate-600">{{ $item['sheet'] }}</td>
                                <td class="px-5 py-2.5">
                                    @php
                                        $catClasses = [
                                            'Gagal' => 'text-red-600 bg-red-50',
                                            'Skip Duplikat' => 'text-orange-600 bg-orange-50',
                                            'Skip Kosong' => 'text-amber-600 bg-amber-50',
                                            'Unregistered' => 'text-slate-600 bg-slate-50',
                                            'Skip' => 'text-orange-600 bg-orange-50',
                                        ];
                                        $catClass = $catClasses[$item['kategori']] ?? 'text-slate-600 bg-slate-50';
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $catClass }}">
                                        {{ $item['kategori'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-xs text-slate-700">{{ $item['pesan'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6 text-center">
            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-slate-400">Tidak ada error atau baris yang di-skip. Semua baris berhasil diproses.</p>
        </div>
    @endif

    {{-- Tombol Aksi --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('cm.imports.history') }}"
           class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
            Kembali ke Riwayat Import
        </a>
        @if ($importLog->status === 'completed')
            <form method="POST" action="{{ route('cm.imports.undo', $importLog) }}" class="inline" id="undoForm">
                @csrf
                <button type="submit"
                        class="px-4 py-2 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors undo-btn">
                    Undo Import
                </button>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var undoForm = document.getElementById('undoForm');
    if (undoForm) {
        undoForm.addEventListener('submit', function(e) {
            if (!confirm('Yakin ingin meng-undo import ini? Semua data yang dihasilkan dari import ini akan dihapus.')) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
