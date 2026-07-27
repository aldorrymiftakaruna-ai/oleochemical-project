@forelse($findings as $finding)
    <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-sm transition-shadow">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-sm font-semibold text-slate-900">{{ $finding->equipment->equipment_tag ?? '-' }}</span>
                    <span class="text-xs text-slate-400">{{ $finding->equipment->pt_location ?? '' }}</span>

                    @php
                        $sevColors = ['low' => 'bg-blue-100 text-blue-700', 'medium' => 'bg-amber-100 text-amber-700', 'high' => 'bg-red-100 text-red-700'];
                        $sevColor = $sevColors[$finding->severity] ?? 'bg-slate-100 text-slate-600';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sevColor }}">
                        {{ ucfirst($finding->severity) }}
                    </span>

                    @if($finding->status === 'open')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Open
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Closed
                        </span>
                    @endif
                </div>

                <p class="text-sm text-slate-700 mb-2">{{ $finding->deskripsi }}</p>

                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ $finding->kategori }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $finding->pic }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $finding->tanggal_temuan->format('d M Y') }}
                    </span>
                    @if($finding->status === 'open')
                        <span class="flex items-center gap-1 {{ $finding->hari_open > 7 ? 'text-red-500 font-medium' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $finding->hari_open }} hari open
                        </span>
                    @endif
                </div>
            </div>

            @if($finding->foto_url)
                <button onclick="openModal('findingPhoto{{ $finding->id }}')"
                        class="shrink-0 w-16 h-16 rounded-lg border border-slate-200 overflow-hidden hover:opacity-80 transition-opacity">
                    <img src="{{ $finding->foto_url }}" alt="Foto temuan" class="w-full h-full object-cover">
                </button>

                <x-modal name="findingPhoto{{ $finding->id }}" title="Detail Finding — {{ $finding->equipment->equipment_tag ?? '' }}" maxWidth="lg">
                    <div class="space-y-4">
                        @if($finding->foto_url)
                            <img src="{{ $finding->foto_url }}" alt="Foto" class="w-full rounded-lg">
                        @endif
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-400 text-xs">Kategori</span>
                                <p class="font-medium">{{ $finding->kategori }}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 text-xs">Tanggal Temuan</span>
                                <p class="font-medium">{{ $finding->tanggal_temuan->format('d M Y') }}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 text-xs">PIC</span>
                                <p class="font-medium">{{ $finding->pic }}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 text-xs">Severity</span>
                                <p class="font-medium capitalize">{{ $finding->severity }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-700">{{ $finding->deskripsi }}</p>
                        <button onclick="closeModal('findingPhoto{{ $finding->id }}')"
                                class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg">
                            Tutup
                        </button>
                    </div>
                </x-modal>
            @endif
        </div>
    </div>
@empty
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-sm text-slate-400">
        Tidak ada finding ditemukan.
    </div>
@endforelse
