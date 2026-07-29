<?php $__env->startSection('title', $asset->tech_ident_no ?? $asset->equipment_no ?? 'Detail Asset'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo e(route('assets.index')); ?>" class="hover:text-slate-700">Asset Management</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium"><?php echo e($asset->tech_ident_no ?? $asset->equipment_no ?? 'Detail Asset'); ?></span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('header-actions'); ?>
    <a href="<?php echo e(route('assets.edit', $asset)); ?>"
       class="inline-flex items-center gap-2 px-4 py-2 border border-amber-300 hover:bg-amber-50 text-amber-700 text-sm font-medium rounded-lg transition-colors">
        Edit Asset
    </a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div x-data="assetDetail()" class="space-y-6">

        <!-- Ringkasan Info Asset + Kartu Statistik -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Info Asset -->
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Informasi Asset</h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <?php if($asset->equipment_no): ?>
                                    EQ: <span class="font-mono"><?php echo e($asset->equipment_no); ?></span>
                                <?php endif; ?>
                                <?php if($asset->functional_loc): ?>
                                    &middot; FL: <span class="font-mono"><?php echo e($asset->functional_loc); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $asset->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($asset->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                    </div>
                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                        <div class="md:col-span-3">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Deskripsi</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?php echo e($asset->description ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Tech Ident No</dt>
                            <dd class="mt-1 font-mono text-sm font-semibold text-slate-900"><?php echo e($asset->tech_ident_no ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Object Type</dt>
                            <dd class="mt-1">
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-700 rounded"><?php echo e($asset->object_type ?? '-'); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Data Source</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?php echo e($asset->data_source === 'import_excel' ? 'Import Excel' : 'Manual'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Manufacturer</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?php echo e($asset->manufacturer ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Model Number</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?php echo e($asset->model_number ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Construct Year</dt>
                            <dd class="mt-1 text-sm text-slate-900"><?php echo e($asset->construct_year ?? '-'); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Lokasi -->
            <div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 h-full">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Lokasi</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Company
                            </div>
                            <p class="text-sm font-medium text-slate-900"><?php echo e($asset->company?->code ?? '-'); ?></p>
                            <?php if($asset->company): ?><p class="text-xs text-slate-400"><?php echo e($asset->company->name); ?></p><?php endif; ?>
                        </div>
                        <?php if($asset->department): ?>
                        <div class="border-t border-slate-100 pt-3">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">Departemen</div>
                            <p class="text-sm text-slate-900"><?php echo e($asset->department->code); ?> — <?php echo e($asset->department->name); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if($asset->area): ?>
                        <div class="border-t border-slate-100 pt-3">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">Area</div>
                            <p class="text-sm font-medium text-slate-900"><?php echo e($asset->area->code); ?> — <?php echo e($asset->area->name); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if($asset->subArea): ?>
                        <div class="border-t border-slate-100 pt-3">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">Sub Area</div>
                            <p class="text-sm text-slate-900"><?php echo e($asset->subArea->code); ?> — <?php echo e($asset->subArea->name); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if($asset->functional_loc): ?>
                        <div class="border-t border-slate-100 pt-3">
                            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">Functional Loc</div>
                            <p class="text-sm font-mono text-slate-900"><?php echo e($asset->functional_loc); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Statistik -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
                <p class="text-3xl font-bold text-slate-900"><?php echo e($totalReports); ?></p>
                <p class="text-xs text-slate-500 mt-1">Total Perbaikan</p>
            </div>
            <div class="bg-white rounded-xl border border-green-200 p-5 text-center">
                <p class="text-3xl font-bold text-green-600"><?php echo e($completedReports); ?></p>
                <p class="text-xs text-slate-500 mt-1">Selesai</p>
            </div>
            <div class="bg-white rounded-xl border border-amber-200 p-5 text-center">
                <p class="text-3xl font-bold text-amber-600"><?php echo e($needsReviewReports); ?></p>
                <p class="text-xs text-slate-500 mt-1">Perlu Review</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-200 p-5 text-center">
                <p class="text-3xl font-bold text-blue-600"><?php echo e($technicians->count()); ?></p>
                <p class="text-xs text-slate-500 mt-1">Teknisi Pernah Repair</p>
            </div>
            <div class="bg-white rounded-xl border border-purple-200 p-5 text-center">
                <p class="text-3xl font-bold text-purple-600"><?php echo e($asset->technicians->count()); ?></p>
                <p class="text-xs text-slate-500 mt-1">Teknisi Ditugaskan</p>
            </div>
        </div>

        <!-- Panel Teknisi Ditugaskan -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Teknisi Ditugaskan
                </h3>
                <button @click="openAssignModal"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Teknisi
                </button>
            </div>
            <div class="px-6 py-4">
                <?php if($asset->technicians->isNotEmpty()): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php $__currentLoopData = $asset->technicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignedTech): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                        <?php echo e(substr($assignedTech->name, 0, 1)); ?>

                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate"><?php echo e($assignedTech->name); ?></p>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                            <?php if($assignedTech->nik): ?><span><?php echo e($assignedTech->nik); ?></span><?php endif; ?>
                                            <?php if($assignedTech->group): ?>
                                                <span>&middot;</span>
                                                <span class="text-purple-500"><?php echo e(\App\Models\Technician::GROUPS[$assignedTech->group] ?? $assignedTech->group); ?></span>
                                            <?php endif; ?>
                                            <?php if($assignedTech->section): ?>
                                                <span>&middot;</span>
                                                <span class="text-blue-500"><?php echo e(\App\Models\Technician::SECTIONS[$assignedTech->section] ?? $assignedTech->section); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($assignedTech->pivot->note): ?>
                                            <p class="text-xs text-slate-500 mt-0.5 italic truncate"><?php echo e($assignedTech->pivot->note); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button onclick="removeTechnician(<?php echo e($asset->id); ?>, <?php echo e($assignedTech->id); ?>)"
                                        class="text-red-400 hover:text-red-600 transition-colors p-1 shrink-0" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6 text-sm text-slate-400">
                        Belum ada teknisi yang ditugaskan untuk asset ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grafik -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Perbaikan per Tipe</h3>
                <?php if($statsByType->isNotEmpty()): ?>
                    <div class="space-y-3">
                        <?php
                            $maxType = $statsByType->max();
                            $colors = ['bg-blue-500', 'bg-amber-500', 'bg-green-500', 'bg-red-500', 'bg-purple-500', 'bg-teal-500'];
                            $colorIndex = 0;
                            $typeLabels = ['breakdown' => 'Kerusakan', 'preventive' => 'Preventif', 'corrective' => 'Korektif', 'inspection' => 'Inspeksi', 'overhaul' => 'Overhaul'];
                        ?>
                        <?php $__currentLoopData = $statsByType; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pct = $maxType > 0 ? round(($count / $maxType) * 100) : 0;
                                $color = $colors[$colorIndex % count($colors)];
                                $colorIndex++;
                            ?>
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-slate-700"><?php echo e($typeLabels[$type] ?? $type); ?></span>
                                    <span class="font-medium text-slate-900"><?php echo e($count); ?></span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="<?php echo e($color); ?> h-2 rounded-full transition-all" style="width: <?php echo e($pct); ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="flex items-center justify-center h-32 text-sm text-slate-400">Belum ada data perbaikan</div>
                <?php endif; ?>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Riwayat Perbaikan per Bulan</h3>
                <?php if($statsByMonth->isNotEmpty()): ?>
                    <?php $maxMonth = $statsByMonth->max(); ?>
                    <div class="flex items-end gap-2 h-40">
                        <?php $__currentLoopData = $statsByMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pct = $maxMonth > 0 ? ($count / $maxMonth) * 100 : 10;
                                $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M');
                            ?>
                            <div class="flex-1 flex flex-col items-center">
                                <span class="text-xs font-medium text-slate-700 mb-1"><?php echo e($count); ?></span>
                                <div class="w-full bg-blue-100 rounded-t relative" style="height: 160px;">
                                    <div class="absolute bottom-0 left-0 right-0 bg-blue-500 rounded-t transition-all" style="height: <?php echo e($pct); ?>%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1"><?php echo e($monthLabel); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="flex items-center justify-center h-32 text-sm text-slate-400">Belum ada data perbaikan</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Teknisi yang pernah memperbaiki -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Teknisi yang Pernah Memperbaiki</h3>
            </div>
            <?php if($technicians->isNotEmpty()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-6 py-3">Teknisi</th>
                                <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-6 py-3">NIK</th>
                                <th class="text-center text-xs font-medium text-slate-500 uppercase tracking-wide px-6 py-3">Jumlah</th>
                                <th class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide px-6 py-3">Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php $__currentLoopData = $technicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $technician): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0"><?php echo e(substr($technician->name, 0, 1)); ?></div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-900"><?php echo e($technician->name); ?></p>
                                                <?php if($technician->telegram_username): ?><p class="text-xs text-slate-400">@ <?php echo e($technician->telegram_username); ?></p><?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-sm text-slate-700"><?php echo e($technician->nik ?? '-'); ?></td>
                                    <td class="px-6 py-3.5 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-50 text-blue-700 text-sm font-bold rounded-full"><?php echo e($technician->repair_count); ?></span>
                                    </td>
                                    <td class="px-6 py-3.5 text-sm text-slate-600"><?php echo e($technician->last_repair?->format('d M Y') ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-10 text-center text-sm text-slate-400">Belum ada teknisi yang melaporkan perbaikan untuk asset ini.</div>
            <?php endif; ?>
        </div>

        <!-- Riwayat Perbaikan -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Riwayat Perbaikan</h3>
            </div>
            <?php if($reports->isNotEmpty()): ?>
                <div class="divide-y divide-slate-50">
                    <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-medium text-slate-900"><?php echo e($report->technician?->name ?? 'Unknown'); ?></span>
                                        <span class="text-xs text-slate-400">&middot;</span>
                                        <span class="text-xs text-slate-500"><?php echo e($report->report_date->format('d M Y')); ?></span>
                                        <?php if($report->report_type): ?>
                                            <span class="text-xs text-slate-400">&middot;</span>
                                            <span class="inline-flex px-2 py-0.5 text-[10px] font-medium bg-slate-100 text-slate-600 rounded"><?php echo e(ucfirst($report->report_type)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-slate-700 leading-relaxed"><?php echo e($report->work_description); ?></p>
                                    <?php if($report->status === 'completed' && $report->completed_at): ?>
                                        <p class="text-xs text-green-600 mt-1">Selesai <?php echo e($report->completed_at->format('d M Y H:i')); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="shrink-0"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $report->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($report->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="px-6 py-10 text-center text-sm text-slate-400">Belum ada riwayat perbaikan untuk asset ini.</div>
            <?php endif; ?>
        </div>

        <!-- Modal Assign Teknisi -->
        <div x-show="showAssignModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             x-cloak>
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full max-h-[80vh] overflow-hidden"
                 @click.away="showAssignModal = false">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Tambah Teknisi</h3>
                    <button @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Pilih Teknisi</label>
                        <select x-model="selectedTechnicianId" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                            <option value="">-- Pilih Teknisi --</option>
                            <?php $__currentLoopData = $allTechnicians; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tech): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tech->id); ?>"><?php echo e($tech->name); ?> <?php if($tech->nik): ?>(<?php echo e($tech->nik); ?>)<?php endif; ?> <?php if($tech->telegram_username): ?> — {{ $tech->telegram_username }}<?php endif; ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1.5">Catatan Tugas</label>
                        <textarea x-model="assignNote" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Contoh: Mohon cek rutin pompa di area 6600"></textarea>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Jika teknisi memiliki akun Telegram aktif, notifikasi akan dikirim otomatis.
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                    <button @click="showAssignModal = false" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg">Batal</button>
                    <button @click="assignTechnician" :disabled="!selectedTechnicianId"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg">
                        <span x-show="!assigning">Tambahkan & Kirim Notifikasi</span>
                        <span x-show="assigning" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Menambahkan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function assetDetail() {
    return {
        showAssignModal: false,
        selectedTechnicianId: '',
        assignNote: '',
        assigning: false,

        openAssignModal() {
            this.showAssignModal = true;
            this.selectedTechnicianId = '';
            this.assignNote = '';
        },

        assignTechnician() {
            if (!this.selectedTechnicianId) return;
            this.assigning = true;

            fetch('<?php echo e(route('assets.technicians.assign', $asset)); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    technician_id: this.selectedTechnicianId,
                    note: this.assignNote,
                }),
            })
            .then(res => res.json())
            .then(data => {
                this.showAssignModal = false;
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => alert('Terjadi kesalahan jaringan.'))
            .finally(() => { this.assigning = false; });
        },
    };
}

function removeTechnician(assetId, technicianId) {
    if (!confirm('Hapus teknisi ini dari asset?')) return;
    fetch(`/assets/${assetId}/technicians/${technicianId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(data => { if (data.success) location.reload(); else alert('Gagal menghapus teknisi.'); })
    .catch(err => alert('Terjadi kesalahan.'));
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/assets/show.blade.php ENDPATH**/ ?>