<div class="py-12">
    <div class="max-w-2xl mx-auto text-center">
        <div class="mb-8">
            <div class="text-9xl font-bold text-slate-200 mb-4">404</div>
            <h1 class="text-4xl font-bold text-slate-900 mb-2">Page Not Found</h1>
            <p class="text-lg text-slate-600">
                <?php echo isset($message) ? htmlspecialchars($message) : 'Sorry, the page you are looking for does not exist.'; ?>
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/"
               class="inline-flex items-center justify-center px-6 py-3 text-white bg-blue-600 hover:bg-blue-700 rounded-md font-medium transition-colors"
            >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
                Return Home
            </a>
        </div>
    </div>
</div>
