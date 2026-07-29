<?php $__env->startSection('title', $finding->kode_finding . ' — Detail Finding'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo e(route('cm.overview')); ?>" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo e(route('cm.findings')); ?>" class="hover:text-slate-700">Finding CM</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium"><?php echo e($finding->kode_finding); ?></span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('cm._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php
        $sevColors = ['low' => 'bg-blue-100 text-blue-700', 'medium' => 'bg-amber-100 text-amber-700', 'high' => 'bg-red-100 text-red-700'];
        $sevColor = $sevColors[$finding->severity] ?? 'bg-slate-100 text-slate-600';
        $wo = $finding->workOrder;
    ?>

    
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-2xl font-bold font-mono" style="color: #0E9E8E;"><?php echo e($finding->kode_finding); ?></h2>
                    <span class="inline-flex px-2.5 py-0.5 text-xs font-medium rounded-full <?php echo e($sevColor); ?>"><?php echo e(ucfirst($finding->severity)); ?></span>
                    <?php if($finding->status === 'open'): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Open
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Closed
                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-slate-500 mt-2">
                    Equipment:
                    <?php if($finding->equipment): ?>
                        <a href="<?php echo e(route('cm.equipment-show', $finding->equipment->equipment_tag)); ?>"
                           class="font-mono font-semibold text-teal-600 hover:text-teal-800 hover:underline">
                            <?php echo e($finding->equipment->equipment_tag); ?>

                        </a>
                        &middot; <?php echo e($finding->equipment->pt_location); ?>

                    <?php else: ?>
                        <span class="text-slate-400">(equipment dihapus)</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="shrink-0 flex items-center gap-2">
                <a href="<?php echo e(route('cm.findings')); ?>" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
                <?php if(!$wo && $finding->status === 'open'): ?>
                    <a href="<?php echo e(route('work-orders.create', ['equipment_tag' => $finding->equipment?->equipment_tag, 'linked_finding_id' => $finding->id])); ?>"
                       class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + Work Order
                    </a>
                <?php elseif($wo): ?>
                    <a href="<?php echo e(route('work-orders.show', $wo)); ?>"
                       class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Lihat WO
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Informasi Finding</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Kategori</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo e($finding->kategori ?? '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">PIC</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo e($finding->pic ?? '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Tanggal Temuan</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo e($finding->tanggal_temuan ? $finding->tanggal_temuan->format('d M Y') : '—'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Hari Open</p>
                    <p class="text-sm font-medium <?php echo e($finding->status === 'open' && $finding->hari_open > 7 ? 'text-red-600' : 'text-slate-800'); ?>">
                        <?php echo e($finding->status === 'open' ? ($finding->hari_open . ' hari') : '—'); ?>

                    </p>
                </div>
                <?php if($finding->date_action): ?>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Tanggal Tindakan</p>
                    <p class="text-sm font-medium text-slate-800"><?php echo e($finding->date_action->format('d M Y')); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="lg:col-span-2 space-y-6">
            <?php if($finding->deskripsi): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Deskripsi</h3>
                <p class="text-sm text-slate-700 leading-relaxed"><?php echo e($finding->deskripsi); ?></p>
            </div>
            <?php endif; ?>

            <?php if($finding->analysis): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Analisis</h3>
                <p class="text-sm text-slate-700 leading-relaxed"><?php echo e($finding->analysis); ?></p>
            </div>
            <?php endif; ?>

            <?php if($finding->action): ?>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Tindakan</h3>
                <p class="text-sm text-slate-700 leading-relaxed"><?php echo e($finding->action); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php
        $allPhotos = [];
        if ($finding->foto_url) {
            $allPhotos[] = $finding->foto_url;
        }
        if ($finding->foto_urls && is_array($finding->foto_urls)) {
            foreach ($finding->foto_urls as $url) {
                if (!in_array($url, $allPhotos)) {
                    $allPhotos[] = $url;
                }
            }
        }
        $photoCount = count($allPhotos);
    ?>
    <?php if($photoCount > 0): ?>
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-4">
            Foto
            <?php if($photoCount > 1): ?>
                <span class="text-xs font-normal text-slate-400">(<?php echo e($photoCount); ?> foto)</span>
            <?php endif; ?>
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <?php $__currentLoopData = $allPhotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $photoUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="relative group rounded-lg border border-slate-200 overflow-hidden bg-slate-50">
                <img src="<?php echo e($photoUrl); ?>"
                     alt="Foto Finding <?php echo e($idx + 1); ?>"
                     class="w-full aspect-square object-cover cursor-pointer hover:opacity-90 transition-opacity"
                     onclick="openModal('findingDetailPhoto<?php echo e($finding->id); ?>_<?php echo e($idx); ?>')">
                <?php if($photoCount > 1): ?>
                <div class="absolute top-1.5 left-1.5 bg-black/50 text-white text-[10px] font-medium px-1.5 py-0.5 rounded">
                    <?php echo e($idx + 1); ?>/<?php echo e($photoCount); ?>

                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <?php $__currentLoopData = $allPhotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $photoUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'findingDetailPhoto'.e($finding->id).'_'.e($idx).'','title' => 'Foto Finding — '.e($finding->kode_finding).' ('.e($idx + 1).'/'.e($photoCount).')','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'findingDetailPhoto'.e($finding->id).'_'.e($idx).'','title' => 'Foto Finding — '.e($finding->kode_finding).' ('.e($idx + 1).'/'.e($photoCount).')','maxWidth' => 'lg']); ?>
        <div class="space-y-4">
            <img src="<?php echo e($photoUrl); ?>" alt="Foto Finding <?php echo e($idx + 1); ?>" class="w-full rounded-lg">
            <div class="flex items-center justify-between">
                <div class="text-xs text-slate-400">
                    <?php if($idx > 0): ?>
                    <button onclick="openModal('findingDetailPhoto<?php echo e($finding->id); ?>_<?php echo e($idx - 1); ?>'); closeModal('findingDetailPhoto<?php echo e($finding->id); ?>_<?php echo e($idx); ?>')"
                            class="px-3 py-1.5 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Sebelumnya
                    </button>
                    <?php endif; ?>
                    <?php if($idx < $photoCount - 1): ?>
                    <button onclick="openModal('findingDetailPhoto<?php echo e($finding->id); ?>_<?php echo e($idx + 1); ?>'); closeModal('findingDetailPhoto<?php echo e($finding->id); ?>_<?php echo e($idx); ?>')"
                            class="px-3 py-1.5 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1">
                        Selanjutnya
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <?php endif; ?>
                </div>
                <button onclick="closeModal('findingDetailPhoto<?php echo e($finding->id); ?>_<?php echo e($idx); ?>')"
                        class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                    Tutup
                </button>
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    
    <?php if($wo): ?>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Work Order Terkait</h3>
        </div>
        <div class="p-5">
            <div class="flex items-center justify-between">
                <div>
                    <a href="<?php echo e(route('work-orders.show', $wo)); ?>" class="font-mono font-semibold text-teal-600 hover:text-teal-800 hover:underline">
                        <?php echo e($wo->wo_number ?? '#' . $wo->id); ?>

                    </a>
                    <p class="text-xs text-slate-500 mt-0.5"><?php echo e($wo->jenis_pekerjaan ?? '—'); ?></p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full
                    <?php echo e($wo->status === 'open' ? 'bg-amber-100 text-amber-700' : ($wo->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700')); ?>">
                    <?php echo e(ucfirst($wo->status ?? '—')); ?>

                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/cm/finding-show.blade.php ENDPATH**/ ?>