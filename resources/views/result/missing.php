<div class="py-12">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <svg class="h-16 w-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h1 class="text-4xl font-bold text-slate-900 mb-2">Result Not Found</h1>
            <p class="text-lg text-slate-600 mb-4">
                <?php echo isset($message) ? htmlspecialchars($message) : 'The analysis result you are looking for could not be found.'; ?>
            </p>
        </div>

        <div class="bg-blue-50 rounded-lg p-6 border border-blue-200 mb-8">
            <div class="flex gap-3">
                <svg class="h-6 w-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-1">Why is this happening?</h3>
                    <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                        <li>Results are cached for a limited time and may have expired</li>
                        <li>The analysis ID may be incorrect or malformed</li>
                        <li>Your session may have been cleared</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/"
               class="inline-flex items-center justify-center px-6 py-3 text-white bg-blue-600 hover:bg-blue-700 rounded-md font-medium transition-colors"
            >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Start New Analysis
            </a>
            <button
                @click="window.history.back()"
                class="inline-flex items-center justify-center px-6 py-3 text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 font-medium transition-colors"
            >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Go Back
            </button>
        </div>

        <div class="mt-12 p-6 bg-slate-50 rounded-lg border border-slate-200">
            <h3 class="font-semibold text-slate-900 mb-3">Troubleshooting Tips</h3>
            <ul class="space-y-2 text-sm text-slate-600">
                <li class="flex items-start gap-3">
                    <span class="font-bold text-slate-400 min-w-max">1.</span>
                    <span>Check that you have the correct analysis URL. The ID may have changed.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="font-bold text-slate-400 min-w-max">2.</span>
                    <span>If you recently cleared your cookies or browser data, previous analysis results may be unavailable.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="font-bold text-slate-400 min-w-max">3.</span>
                    <span>Try running a new analysis on the same procurement URL - results are automatically cached.</span>
                </li>
            </ul>
        </div>
    </div>
</div>
