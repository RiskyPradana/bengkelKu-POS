<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>BengkelOS Mobile</title>
    <link rel="manifest" href="/manifest.json">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="h-full bg-slate-900 text-white">


<header class="fixed top-0 inset-x-0 z-50 bg-slate-900/95 backdrop-blur border-b border-slate-800 safe-area-top">
    <div class="flex items-center px-4 h-14 gap-3">
        <span class="text-amber-400 text-lg font-bold tracking-tight">&#x1F527; BengkelOS</span>
        <div class="flex-1"></div>
        <span class="text-slate-400 text-xs"><?php echo e(auth()->user()?->name); ?></span>
    </div>
</header>


<main class="pt-14 pb-20 min-h-full">
    <?php echo e($slot); ?>

</main>


<nav class="fixed bottom-0 inset-x-0 z-50 bg-slate-900/95 backdrop-blur border-t border-slate-800 safe-area-bottom">
    <div class="grid grid-cols-3 h-16">
        <a href="/mobile" class="flex flex-col items-center justify-center gap-1 text-xs <?php echo e(request()->is('mobile') ? 'text-amber-400' : 'text-slate-400'); ?>">
            <span class="text-xl">&#x1F3E0;</span>
            <span>Beranda</span>
        </a>
        <a href="/mobile/scanner" class="flex flex-col items-center justify-center gap-1 text-xs <?php echo e(request()->is('mobile/scanner') ? 'text-amber-400' : 'text-slate-400'); ?>">
            <span class="text-xl">&#x1F4F7;</span>
            <span>Scan</span>
        </a>
        <a href="/mobile/wo" class="flex flex-col items-center justify-center gap-1 text-xs <?php echo e(request()->is('mobile/wo') ? 'text-amber-400' : 'text-slate-400'); ?>">
            <span class="text-xl">&#x1F4CB;</span>
            <span>Work Order</span>
        </a>
    </div>
</nav>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

<script>
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
</script>
</body>
</html>
<?php /**PATH D:\FREELANCE\NONA\BengkelKu-POS\resources\views/layouts/mobile.blade.php ENDPATH**/ ?>