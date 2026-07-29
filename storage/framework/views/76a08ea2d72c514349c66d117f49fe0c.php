<?php if($findings->count()): ?>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Equipment</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Severity</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Tgl Temuan</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Hari</th>
                        <th class="text-right px-4 py-2.5 text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $findings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $finding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $sevColors = ['low' => 'bg-blue-100 text-blue-700', 'medium' => 'bg-amber-100 text-amber-700', 'high' => 'bg-red-100 text-red-700'];
                            $sevColor = $sevColors[$finding->severity] ?? 'bg-slate-100 text-slate-600';
                            $wo = $finding->workOrder()->first();
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <?php if($finding->foto_url): ?>
                                        <button onclick="openModal('findingPhoto<?php echo e($finding->id); ?>')"
                                                class="shrink-0 w-8 h-8 rounded border border-slate-200 overflow-hidden hover:opacity-80">
                                            <img src="<?php echo e($finding->foto_url); ?>" alt="Foto" class="w-full h-full object-cover">
                                        </button>

                                        <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => 'findingPhoto'.e($finding->id).'','title' => 'Detail Finding — '.e($finding->equipment->equipment_tag ?? '').'','maxWidth' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'findingPhoto'.e($finding->id).'','title' => 'Detail Finding — '.e($finding->equipment->equipment_tag ?? '').'','maxWidth' => 'lg']); ?>
                                            <div class="space-y-4">
                                                <?php if($finding->foto_url): ?>
                                                    <img src="<?php echo e($finding->foto_url); ?>" alt="Foto" class="w-full rounded-lg">
                                                <?php endif; ?>
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-slate-400 text-xs">Kategori</span>
                                                        <p class="font-medium"><?php echo e($finding->kategori); ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-400 text-xs">Tanggal Temuan</span>
                                                        <p class="font-medium"><?php echo e($finding->tanggal_temuan->format('d M Y')); ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-400 text-xs">PIC</span>
                                                        <p class="font-medium"><?php echo e($finding->pic); ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-400 text-xs">Severity</span>
                                                        <p class="font-medium capitalize"><?php echo e($finding->severity); ?></p>
                                                    </div>
                                                </div>
                                                <p class="text-sm text-slate-700"><?php echo e($finding->deskripsi); ?></p>
                                                <button onclick="closeModal('findingPhoto<?php echo e($finding->id); ?>')"
                                                        class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg">
                                                    Tutup
                                                </button>
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
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-mono font-semibold">
                                            <a href="<?php echo e(route('cm.equipment-show', $finding->equipment->equipment_tag ?? '-')); ?>"
                                               class="text-teal-700 hover:text-teal-900 hover:underline transition-colors">
                                                <?php echo e($finding->equipment->equipment_tag ?? '-'); ?>

                                            </a>
                                        </div>
                                        <div class="text-xs text-slate-400"><?php echo e($finding->equipment->pt_location ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-700 max-w-[250px] truncate"><?php echo e($finding->deskripsi); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium <?php echo e($sevColor); ?>">
                                    <?php echo e(ucfirst($finding->severity)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if($finding->status === 'open'): ?>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Open
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Closed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500 text-xs"><?php echo e($finding->tanggal_temuan->format('d M Y')); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if($finding->status === 'open'): ?>
                                    <span class="text-xs <?php echo e($finding->hari_open > 7 ? 'text-red-500 font-medium' : 'text-slate-400'); ?>">
                                        <?php echo e($finding->hari_open); ?> hari
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <?php if($wo): ?>
                                    <a href="<?php echo e(route('work-orders.show', $wo)); ?>"
                                       class="inline-block px-2.5 py-1 text-xs font-medium text-teal-600 hover:bg-teal-50 rounded-md transition-colors">
                                        Lihat WO
                                    </a>
                                <?php elseif($finding->status === 'open'): ?>
                                    <a href="<?php echo e(route('work-orders.create', ['equipment_tag' => $finding->equipment->equipment_tag, 'linked_finding_id' => $finding->id])); ?>"
                                       class="inline-block px-2.5 py-1 text-xs font-medium text-amber-600 hover:bg-amber-50 rounded-md transition-colors">
                                        + WO
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-sm text-slate-400">
        Tidak ada finding ditemukan.
    </div>
<?php endif; ?>

<?php if(method_exists($findings, 'links')): ?>
    <div class="mt-4">
        <?php echo e($findings->links()); ?>

    </div>
<?php endif; ?>

<?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/cm/_findings-list.blade.php ENDPATH**/ ?>