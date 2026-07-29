<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'modal',
    'title' => '',
    'maxWidth' => 'md',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name' => 'modal',
    'title' => '',
    'maxWidth' => 'md',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $modalId = 'modal-' . $name;
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
    ];
    $width = $widths[$maxWidth] ?? 'max-w-md';
?>

<div id="<?php echo e($modalId); ?>"
     style="display:none"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeModal('<?php echo e($name); ?>')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full <?php echo e($width); ?> p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900"><?php echo e($title); ?></h3>
            <button onclick="closeModal('<?php echo e($name); ?>')" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
                <?php echo e($slot); ?>

    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(name) {
    var el = document.getElementById('modal-' + name);
    if (el) el.style.display = 'flex';
}
function closeModal(name) {
    var el = document.getElementById('modal-' + name);
    if (el) el.style.display = 'none';
}
</script>
<?php $__env->stopPush(); ?>

<?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/components/modal.blade.php ENDPATH**/ ?>