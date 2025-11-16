<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Procurement Insight Analyzer'; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3/tailwind.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600 hover:text-blue-700">
                        Procurement Analyzer
                    </a>
                    <nav class="flex gap-4">
                        <a href="/" class="text-slate-600 hover:text-slate-900 px-3 py-2 rounded-md text-sm font-medium">
                            Home
                        </a>
                    </nav>
                </div>
            </div>
        </nav>

        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <?php if (isset($flash) && is_array($flash)): ?>
                <?php include __DIR__ . '/partials/flash-messages.php'; ?>
            <?php endif; ?>
            
            <?php echo $__content; ?>
        </main>

        <footer class="bg-white border-t border-slate-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <p class="text-center text-slate-600 text-sm">
                    &copy; 2024 Procurement Insight Analyzer. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
