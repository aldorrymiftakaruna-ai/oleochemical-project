<?php $__env->startSection('title', 'Report & Analysis - Condition Monitoring'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo e(route('cm.overview')); ?>" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">Report & Analysis</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('cm._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Tahun</label>
                <select name="tahun" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <?php $__currentLoopData = $tahunList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $th): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($th); ?>" <?php echo e($filterTahun == $th ? 'selected' : ''); ?>><?php echo e($th); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Bulan</label>
                <select name="bulan" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Bulan</option>
                    <?php $__currentLoopData = $bulanList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bl => $blName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($bl); ?>" <?php echo e($filterBulan == $bl ? 'selected' : ''); ?>><?php echo e($blName); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">PT</label>
                <select name="pt" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua PT</option>
                    <?php $__currentLoopData = $ptList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pt); ?>" <?php echo e($filterPt === $pt ? 'selected' : ''); ?>><?php echo e($pt); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                Terapkan
            </button>
            <a href="<?php echo e(route('cm.report-analysis')); ?>" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                Reset
            </a>
        </form>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Equipment</p>
            <p class="text-2xl font-bold text-slate-900 mt-1"><?php echo e(number_format($totalEquipment)); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Readings</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e(number_format($totalReadings)); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400">Total Findings</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e(number_format($totalFindings)); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-xs text-slate-400">Finding Open</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e(number_format($openFindings)); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-xs text-slate-400">Monitoring <?php echo e($filterTahun); ?></p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($monitoringPct); ?>%</p>
            <p class="text-xs text-slate-400"><?php echo e($monitoringSudah); ?>/<?php echo e($monitoringTotal); ?> bulan</p>
        </div>
    </div>

    
    <?php if($insight): ?>
    <div class="bg-gradient-to-r from-teal-50 to-emerald-50 rounded-xl border border-teal-200 p-5 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold text-teal-900">Insight Analisa Vibrasi - <?php echo e($filterTahun); ?></h4>
                <p class="text-sm text-teal-800 mt-1">
                    <strong><?php echo e($insight['dominant_category']); ?></strong> adalah kategori masalah paling dominan tahun ini
                    (Total: <?php echo e($insight['dominant_total']); ?> kasus, <?php echo e($insight['trend_direction']); ?>).
                    <?php if($insight['top_pt']): ?>
                        <?php echo e($insight['top_pt']); ?> memiliki proporsi <?php echo e($insight['dominant_category']); ?> tertinggi
                        (<?php echo e($insight['top_pt_pct']); ?>% dari equipment Alarm/Danger di PT ini).
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="font-semibold text-slate-900 mb-1">Equipment Vibrasi Tinggi per PT</h3>
        <p class="text-xs text-slate-400 mb-5">
            Status ALARM/DANGER dengan vibrasi &gt; 4.5 mm/s - dikelompokkan berdasarkan kategori analisa
        </p>

        <div class="grid grid-cols-1 <?php echo e($filterPt ? '' : 'lg:grid-cols-3'); ?> gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $ptBreakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt => $ptData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    
                    <div class="bg-slate-50 px-4 py-3 border-b border-slate-200">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-slate-900 text-sm"><?php echo e($pt); ?></h4>
                            <span class="text-xs text-slate-500 bg-white px-2 py-0.5 rounded-full border border-slate-200">
                                <?php echo e($ptData['total']); ?> equipment
                            </span>
                        </div>
                    </div>

                    
                    <div class="divide-y divide-slate-100">
                        <?php $__empty_2 = true; $__currentLoopData = $ptData['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <div class="px-4 py-3">
                                
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-teal-100 text-teal-700 text-xs font-bold flex items-center justify-center">
                                            <?php echo e($cat['rank']); ?>

                                        </span>
                                        <span class="text-sm font-medium text-slate-800"><?php echo e($cat['name']); ?></span>
                                    </div>
                                    <span class="text-xs text-slate-500"><?php echo e($cat['count']); ?> equipment</span>
                                </div>

                                
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex-1 bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-teal-500 h-1.5 rounded-full" style="width: <?php echo e($cat['percentage']); ?>%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-teal-600 w-12 text-right"><?php echo e($cat['percentage']); ?>%</span>
                                </div>

                                
                                <div class="flex flex-wrap gap-1.5">
                                    <?php $__currentLoopData = $cat['equipments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e(route('cm.equipment-show', $eq['tag'])); ?>"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium hover:opacity-80 transition-opacity
                                            <?php echo e($eq['status'] === 'danger' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200'); ?>">
                                            <span class="font-mono"><?php echo e($eq['tag']); ?></span>
                                            <span class="w-1.5 h-1.5 rounded-full inline-block
                                                <?php echo e($eq['status'] === 'danger' ? 'bg-red-500' : 'bg-amber-500'); ?>"></span>
                                            <span class="uppercase text-[10px]"><?php echo e($eq['status'] === 'danger' ? 'DGR' : 'ALR'); ?></span>
                                            <?php if($eq['bulan']): ?>
                                                <span class="text-slate-400">-</span>
                                                <span class="text-slate-400"><?php echo e($eq['bulan']); ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <div class="px-4 py-6 text-center text-sm text-slate-400">
                                Tidak ada data vibrasi tinggi di PT ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full text-center py-8 text-sm text-slate-400">
                    Tidak ada data equipment dengan vibrasi tinggi untuk filter yang dipilih.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="font-semibold text-slate-900 mb-1">Ranking Analisa Bulanan</h3>
        <p class="text-xs text-slate-400 mb-5">
            Trend jumlah equipment vibrasi tinggi per kategori analisa - semua PT (filter: Alarm/Danger, vibrasi &gt; 4.5 mm/s)
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Rank</th>
                        <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori Analisa</th>
                        <?php $labels = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; ?>
                        <?php $__currentLoopData = $allMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-center px-2 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider"><?php echo e($labels[$m] ?? $m); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="text-center px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                        $totalsPerBulan = array_fill_keys($allMonths, 0);
                        $grandTotal = 0;
                        foreach ($rankedTable as $row) {
                            foreach ($allMonths as $m) {
                                $totalsPerBulan[$m] += $row[$m] ?? 0;
                            }
                            $grandTotal += $row['total'];
                        }
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $rankedTable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $isTop3 = $row['rank'] <= 3;
                            $rowBg = $isTop3 ? 'bg-teal-50/40' : '';
                        ?>
                        <tr class="<?php echo e($rowBg); ?> hover:bg-slate-50 transition-colors">
                            <td class="px-3 py-2.5 text-sm font-bold <?php echo e($isTop3 ? 'text-teal-600' : 'text-slate-400'); ?>">
                                #<?php echo e($row['rank']); ?>

                            </td>
                            <td class="px-3 py-2.5 text-sm font-medium text-slate-800">
                                <?php echo e($row['analysis']); ?>

                            </td>
                            <?php $__currentLoopData = $allMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="text-center px-2 py-2.5 text-sm text-slate-600">
                                    <?php echo e($row[$m] ?? 0); ?>

                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center px-3 py-2.5 text-sm font-bold text-slate-900 bg-slate-50/50">
                                <?php echo e($row['total']); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(count($allMonths) + 3); ?>" class="text-center py-8 text-sm text-slate-400">
                                Tidak ada data analisa vibrasi untuk filter yang dipilih.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 bg-slate-50">
                        <td colspan="2" class="px-3 py-2.5 text-sm font-bold text-slate-900">Total</td>
                        <?php $__currentLoopData = $allMonths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center px-2 py-2.5 text-sm font-bold text-slate-900"><?php echo e($totalsPerBulan[$m]); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-center px-3 py-2.5 text-sm font-bold text-slate-900 bg-slate-100"><?php echo e($grandTotal); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="font-medium text-slate-900 mb-4">Ringkasan Periodik</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-slate-700 mb-2">Distribusi Monitoring (<?php echo e($filterTahun); ?>)</h4>
                <div class="space-y-2">
                    <div>
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Sudah Diambil</span>
                            <span><?php echo e($monitoringSudah); ?> bulan (<?php echo e($monitoringPct); ?>%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo e($monitoringPct); ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Belum Diambil</span>
                            <span><?php echo e($monitoringTotal - $monitoringSudah); ?> bulan (<?php echo e(100 - $monitoringPct); ?>%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: <?php echo e(100 - $monitoringPct); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-slate-700 mb-2">Status Equipment Overview</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Total Equipment Terpantau</span>
                        <span class="font-semibold"><?php echo e(number_format($totalEquipment)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total Reading Tersedia</span>
                        <span class="font-semibold"><?php echo e(number_format($totalReadings)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Finding Masih Open</span>
                        <span class="font-semibold text-red-600"><?php echo e(number_format($openFindings)); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Finding Sudah Closed</span>
                        <span class="font-semibold text-green-600"><?php echo e(number_format($totalFindings - $openFindings)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="font-medium text-slate-900 mb-4">Export Laporan Rekap</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="<?php echo e(route('cm.export-readings', ['pt' => $filterPt, 'tahun' => $filterTahun])); ?>"
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
            <a href="<?php echo e(route('cm.export-findings', ['pt' => $filterPt])); ?>"
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
            <a href="<?php echo e(route('cm.export-monitoring', ['tahun' => $filterTahun, 'pt' => $filterPt])); ?>"
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
            <a href="<?php echo e(route('cm.export-analysis', ['tahun' => $filterTahun, 'bulan' => $filterBulan, 'pt' => $filterPt])); ?>"
               class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl hover:border-teal-300 hover:bg-teal-50 transition-colors group">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-900 group-hover:text-teal-700 transition-colors">Export Analisa Vibrasi</p>
                    <p class="text-xs text-slate-500">Breakdown per PT & ranking</p>
                </div>
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/cm/report-analysis.blade.php ENDPATH**/ ?>