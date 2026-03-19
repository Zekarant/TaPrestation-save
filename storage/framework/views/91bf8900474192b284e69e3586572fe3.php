

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'autoHide' => true,
    'duration' => 5000
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
    'autoHide' => true,
    'duration' => 5000
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $messages = [];
    
    if (session('success')) {
        $messages[] = ['type' => 'success', 'content' => session('success')];
    }
    if (session('error')) {
        $messages[] = ['type' => 'error', 'content' => session('error')];
    }
    if (session('warning')) {
        $messages[] = ['type' => 'warning', 'content' => session('warning')];
    }
    if (session('info')) {
        $messages[] = ['type' => 'info', 'content' => session('info')];
    }
    if (session('message')) {
        $messages[] = ['type' => 'info', 'content' => session('message')];
    }
    
    $typeConfig = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-400',
            'icon' => 'check-circle',
            'iconColor' => 'text-green-400',
            'textColor' => 'text-green-800',
            'progressColor' => 'bg-green-400'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-400',
            'icon' => 'times-circle',
            'iconColor' => 'text-red-400',
            'textColor' => 'text-red-800',
            'progressColor' => 'bg-red-400'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-400',
            'icon' => 'exclamation-triangle',
            'iconColor' => 'text-yellow-400',
            'textColor' => 'text-yellow-800',
            'progressColor' => 'bg-yellow-400'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-400',
            'icon' => 'info-circle',
            'iconColor' => 'text-blue-400',
            'textColor' => 'text-blue-800',
            'progressColor' => 'bg-blue-400'
        ],
    ];
?>

<?php if(count($messages) > 0): ?>
<div
    class="flash-messages fixed right-4 space-y-3 max-w-md w-full px-4 pointer-events-none"
    style="top: calc(var(--site-nav-h, 70px) + env(safe-area-inset-top, 0px) + 0.5rem); z-index: 10020;"
>
    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $config = $typeConfig[$message['type']] ?? $typeConfig['info'];
    ?>
    
    <div x-data="{ 
            show: false, 
            progress: 100,
            init() {
                setTimeout(() => this.show = true, <?php echo e($index * 100); ?>);
                <?php if($autoHide): ?>
                const interval = setInterval(() => {
                    this.progress -= (100 / (<?php echo e($duration); ?> / 50));
                    if (this.progress <= 0) {
                        clearInterval(interval);
                        this.show = false;
                    }
                }, 50);
                <?php endif; ?>
            }
         }"
         x-show="show"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         class="pointer-events-auto <?php echo e($config['bg']); ?> border-l-4 <?php echo e($config['border']); ?> rounded-r-lg shadow-lg overflow-hidden"
         role="alert">
        
        <div class="p-4">
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <i class="fas fa-<?php echo e($config['icon']); ?> <?php echo e($config['iconColor']); ?> text-xl"></i>
                </div>
                
                <!-- Content -->
                <div class="ml-3 flex-1">
                    <p class="<?php echo e($config['textColor']); ?> text-sm font-medium">
                        <?php echo e($message['content']); ?>

                    </p>
                </div>
                
                <!-- Close button -->
                <button @click="show = false" class="ml-4 flex-shrink-0 <?php echo e($config['iconColor']); ?> hover:opacity-75 transition-opacity">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Progress bar -->
        <?php if($autoHide): ?>
        <div class="h-1 bg-gray-200">
            <div class="<?php echo e($config['progressColor']); ?> h-full transition-all duration-50 ease-linear"
                 :style="{ width: progress + '%' }"></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php /**PATH D:\wamp64\www\TaPrestation-master - Copie\resources\views/components/flash-message.blade.php ENDPATH**/ ?>