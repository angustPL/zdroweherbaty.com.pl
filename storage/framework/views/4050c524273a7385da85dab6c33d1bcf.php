<?php
/**
 * @var bool $enabled
 * @var string $id
 * @var string $domain
 * @var \Spatie\GoogleTagManager\DataLayer $dataLayer
 * @var iterable<\Spatie\GoogleTagManager\DataLayer> $pushData
 */
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($enabled): ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        <?php if (! (empty($dataLayer->toArray()))): ?>
        window.dataLayer.push(<?php echo $dataLayer->toJson(); ?>);
        <?php endif; ?>
    </script>
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;j.src= 'https://<?php echo e($domain); ?>/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo e($id); ?>');
    </script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\www\zdroweherbaty.com.pl-laravel\vendor\spatie\laravel-googletagmanager\src/../resources/views/head.blade.php ENDPATH**/ ?>