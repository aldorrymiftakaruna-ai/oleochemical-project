<?php $__env->startSection('title', 'Finding CM — Condition Monitoring'); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-slate-700">Dashboard</a>
    <span class="text-slate-300">/</span>
    <a href="<?php echo e(route('cm.overview')); ?>" class="hover:text-slate-700">Condition Monitoring</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-900 font-medium">Finding CM</span>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('cm._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-2xl font-bold text-amber-600"><?php echo e($totalOpen); ?></p>
            <p class="text-xs text-slate-500 mt-1">Finding Open</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4">
            <p class="text-2xl font-bold text-red-600"><?php echo e($openOver7); ?></p>
            <p class="text-xs text-slate-500 mt-1">Open &gt; 7 Hari</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4">
            <p class="text-2xl font-bold text-green-600"><?php echo e($totalClosed); ?></p>
            <p class="text-xs text-slate-500 mt-1">Closed</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-2xl font-bold text-slate-900"><?php echo e($totalFindings); ?></p>
            <p class="text-xs text-slate-500 mt-1">Total Finding</p>
        </div>
    </div>











    
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form id="findingsFilterForm" class="flex flex-wrap gap-4 items-end">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">PT</label>
                <select name="pt" class="findings-filter border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua PT</option>
                    <?php $__currentLoopData = $ptList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($pt); ?>" <?php echo e($filterPt === $pt ? 'selected' : ''); ?>><?php echo e($pt); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-500">Status</label>
                <select name="status" class="findings-filter border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Status</option>
                    <option value="open" <?php echo e($filterStatus === 'open' ? 'selected' : ''); ?>>Open</option>
                    <option value="closed" <?php echo e($filterStatus === 'closed' ? 'selected' : ''); ?>>Closed</option>
                </select>
            </div>
            <div class="space-y-1 flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-slate-500">Cari</label>
                <input type="text" name="search" id="findingsSearch" value="<?php echo e($search); ?>"
                       placeholder="Cari tag equipment atau deskripsi..."
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                Filter
            </button>
            <a href="<?php echo e(route('cm.findings')); ?>" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                Reset
            </a>
            <a href="<?php echo e(route('cm.export-findings', ['pt' => $filterPt, 'status' => $filterStatus])); ?>"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
        </form>
    </div>

    
    <div id="findingsList" class="space-y-3">
        <?php echo $__env->make('cm._findings-list', ['findings' => $findings], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var filterForm = document.getElementById('findingsFilterForm');
        var findingsList = document.getElementById('findingsList');
        var debounceTimer;

        /**
         * Ambil semua nilai filter dari form lalu fetch findings via AJAX.
         */
        function loadFindings() {
            var params = new URLSearchParams();
            if (filterForm) {
                var formData = new FormData(filterForm);
                formData.forEach(function(value, key) {
                    if (value) params.set(key, value);
                });
            }

            var url = '<?php echo e(route("cm.findings")); ?>' + '?' + params.toString();
            window.history.replaceState({}, '', url);

                    fetch(url + '&ajax=1')
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    findingsList.innerHTML = html;
                    // Re-attach event listener ke link pagination yang baru
                    attachPaginationHandlers();
                })
                .catch(function(err) {
                    console.error('Error loading findings:', err);
                });
        }

        /**
         * Intercept klik pada link pagination di dalam findingsList
         * supaya pindah halaman via AJAX, bukan reload penuh.
         */
        function attachPaginationHandlers() {
            findingsList.querySelectorAll('a[href*="page="]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    var pageUrl = link.getAttribute('href');
                    if (!pageUrl) return;

                    window.history.replaceState({}, '', pageUrl);

                    var ajaxUrl = pageUrl + (pageUrl.indexOf('?') === -1 ? '?' : '&') + 'ajax=1';
                    fetch(ajaxUrl)
                        .then(function(r) { return r.text(); })
                        .then(function(html) {
                            findingsList.innerHTML = html;
                            attachPaginationHandlers();
                        })
                        .catch(function(err) {
                            console.error('Error loading findings page:', err);
                        });
                });
            });
        }

        // Dropdown PT dan Status — langsung filter saat berubah
        if (filterForm) {
            filterForm.querySelectorAll('.findings-filter').forEach(function(sel) {
                sel.addEventListener('change', function() {
                    loadFindings();
                });
            });

            // Submit form via AJAX
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                loadFindings();
            });
        }

        // Input search — debounce 400ms
        var searchInput = document.getElementById('findingsSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    loadFindings();
                }, 400);
            });
        }

        // Pasang handler pagination saat pertama kali load
        attachPaginationHandlers();
    });
    </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ASUS\oleochemicalReport\resources\views/cm/findings.blade.php ENDPATH**/ ?>