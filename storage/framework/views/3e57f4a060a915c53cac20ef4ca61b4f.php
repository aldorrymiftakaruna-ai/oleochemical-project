<div class="bg-white rounded-xl border border-slate-200 p-4">
    <p class="text-2xl font-bold text-slate-900"><?php echo e(number_format($totalRecords)); ?></p>
    <p class="text-xs text-slate-500 mt-1">Total Records</p>
</div>
<div class="bg-white rounded-xl border border-green-200 p-4">
    <p class="text-2xl font-bold text-green-600"><?php echo e(number_format($goodCount)); ?></p>
    <p class="text-xs text-slate-500 mt-1">Good (<?php echo e($goodPct); ?>%)</p>
</div>
<div class="bg-white rounded-xl border border-amber-200 p-4">
    <p class="text-2xl font-bold text-amber-600"><?php echo e(number_format($alarmCount)); ?></p>
    <p class="text-xs text-slate-500 mt-1">Alarm (<?php echo e($alarmPct); ?>%)</p>
</div>
<div class="bg-white rounded-xl border border-red-200 p-4">
    <p class="text-2xl font-bold text-red-600"><?php echo e(number_format($dangerCount)); ?></p>
    <p class="text-xs text-slate-500 mt-1">Danger (<?php echo e($dangerPct); ?>%)</p>
</div>
<div class="bg-white rounded-xl border border-purple-200 p-4">
    <p class="text-2xl font-bold text-purple-600"><?php echo e(number_format($visualBadCount)); ?></p>
    <p class="text-xs text-slate-500 mt-1">Visual Bad (<?php echo e($visualBadPct); ?>%)</p>
</div>
<?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/cm/_overview-cards.blade.php ENDPATH**/ ?>