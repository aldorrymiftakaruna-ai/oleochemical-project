@extends('layouts.app')

@section('title', 'Riwayat Import — Condition Monitoring')
@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="{{ route('cm.overview') }}" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">Riwayat Import</span>
@endsection

@section('content')
    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('cm.imports.history') }}" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Status</label>
                <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="undone" {{ request('status') === 'undone' ? 'selected' : '' }}>Undone</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Periode</label>
                <select name="periode" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Waktu</option>
                    <option value="7" {{ request('periode') === '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30" {{ request('periode') === '30' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="90" {{ request('periode') === '90' ? 'selected' : '' }}>90 Hari Terakhir</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                Cari
            </button>
            <a href="{{ route('cm.imports.history') }}" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- Tabel Riwayat Import --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">#</th>
                        <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Tgl Upload</th>
                        <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">File</th>
                        <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Status</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Total Baris</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Insert</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Update</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Gagal</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Skip Dup</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Skip Kosong</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Unreg</th>
                        <th class="text-right text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Wkt Proses</th>
                        <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50 transition-colors" data-status="{{ $log->status }}">
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $logs->firstItem() + $loop->index }}</td>
                            <td class="px-5 py-3 text-slate-600 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-slate-900 font-medium text-sm max-w-[200px] truncate" title="{{ $log->nama_file }}">
                                {{ $log->nama_file }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php
                                    $statusClasses = [
                                        'completed' => 'bg-green-100 text-green-700',
                                        'failed'    => 'bg-red-100 text-red-700',
                                        'processing' => 'bg-amber-100 text-amber-700',
                                        'pending'   => 'bg-slate-100 text-slate-500',
                                        'undone'    => 'bg-purple-100 text-purple-600',
                                    ];
                                    $class = $statusClasses[$log->status] ?? 'bg-slate-100 text-slate-500';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $class }} status-badge">
                                    @if ($log->status === 'processing')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse processing-dot"></span>
                                    @endif
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-slate-700 text-sm">{{ number_format($log->total_baris) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-teal-600 text-sm">{{ number_format($log->insert_baru) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-amber-600 text-sm">{{ number_format($log->update_existing) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-red-600 text-sm">{{ number_format($log->gagal) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-orange-600 text-sm">{{ number_format($log->total_skipped_duplikat ?? 0) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-orange-600 text-sm">{{ number_format($log->total_skipped_kosong ?? 0) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-slate-500 text-sm">{{ number_format($log->unregistered) }}</td>
                            <td class="px-5 py-3 text-right text-slate-500 text-xs whitespace-nowrap">
                                @if ($log->status === 'processing')
                                    <span class="processing-duration text-amber-600">Processing...</span>
                                @elseif ($log->completed_at)
                                    {{ $log->completed_at->diffForHumans($log->created_at, ['short' => true]) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('cm.imports.detail', $log) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 border border-teal-300 text-teal-700 rounded-lg text-xs font-medium hover:bg-teal-50 transition-colors">
                                        Detail
                                    </a>
                                    @if ($log->status === 'completed')
                                        <form method="POST" action="{{ route('cm.imports.undo', $log) }}" class="inline undo-form">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-red-300 text-red-600 rounded-lg text-xs font-medium hover:bg-red-50 transition-colors undo-btn">
                                                Undo
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-slate-400 text-sm">Belum ada aktivitas import.</p>
                                <p class="text-slate-400 text-xs mt-1">Upload file Excel CM melalui halaman Overview.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($logs->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function() {
    // Auto-refresh untuk baris berstatus processing
    var processingRows = document.querySelectorAll('tr[data-status="processing"]');

    function refreshProcessingRows() {
        if (processingRows.length === 0) {
            clearInterval(window._importPollInterval);
            return;
        }

        // Hitung durasi
        processingRows.forEach(function(row) {
            var durationEl = row.querySelector('.processing-duration');
            if (durationEl) {
                var currentText = durationEl.textContent;
                var seconds = 0;
                var match = currentText.match(/(\d+)\s*detik/);
                if (match) {
                    seconds = parseInt(match[1]);
                }
                seconds += 10;
                durationEl.textContent = 'Processing... (' + seconds + ' detik)';
            }
        });

        // Reload halaman untuk update status
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            // Parse dan cek apakah masih ada processing
            if (html.includes('data-status="processing"')) {
                // Masih ada processing, biarkan interval lanjut
            } else {
                // Tidak ada processing lagi, reload halaman
                clearInterval(window._importPollInterval);
                window.location.reload();
            }
        })
        .catch(function() {
            // Silently fail
        });
    }

    if (processingRows.length > 0) {
        window._importPollInterval = setInterval(refreshProcessingRows, 10000);
    }

    // Konfirmasi undo
    document.querySelectorAll('.undo-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Yakin ingin meng-undo import ini? Semua data yang dihasilkan dari import ini akan dihapus.')) {
                e.preventDefault();
            }
        });
    });
})();
</script>
@endpush
