<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['path' => null, 'alt' => '', 'class' => 'object-cover w-full h-40', 'placeholder' => '/images/placeholder.svg']));

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

foreach (array_filter((['path' => null, 'alt' => '', 'class' => 'object-cover w-full h-40', 'placeholder' => '/images/placeholder.svg']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $url = null;
    try {
        if ($path) {
            $url = storage_asset_url($path, ltrim($placeholder, '/'));
        }
    } catch (\Exception $e) {
        $url = null;
    }
    $finalSrc = $url ?? asset($placeholder);
?>

<img src="<?php echo e($finalSrc); ?>" alt="<?php echo e($alt); ?>" <?php echo e($attributes->merge(['class' => $class])); ?> data-fallback-src="<?php echo e(asset($placeholder)); ?>" loading="lazy" />
<?php /**PATH D:\wamp64\www\TaPrestation-master - Copie\resources\views/components/media-image.blade.php ENDPATH**/ ?>