<div class="py-8" x-data="{ activeTab: 'analysis' }">
    <div class="mb-8">
        <a href="/" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium mb-4">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Home
        </a>
        <h1 class="text-4xl font-bold text-slate-900 mb-2">
            Analysis Results
        </h1>
        <p class="text-lg text-slate-600">
            Complete procurement analysis and recommendations
        </p>
    </div>

    <!-- Analysis Status -->
    <div class="mb-8">
        <?php include __DIR__ . '/../partials/metadata-status.php'; ?>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-8 flex gap-4 border-b border-slate-200 overflow-x-auto">
        <button
            @click="activeTab = 'analysis'"
            :class="activeTab === 'analysis' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900'"
            class="px-4 py-2 border-b-2 font-medium text-sm transition-colors whitespace-nowrap"
        >
            <svg class="h-5 w-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H3a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1V6a1 1 0 00-1-1h3a1 1 0 000-2 2 2 0 01-2-2H4z" clip-rule="evenodd" />
            </svg>
            Analysis Results
        </button>
        <button
            @click="activeTab = 'procurement'"
            :class="activeTab === 'procurement' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900'"
            class="px-4 py-2 border-b-2 font-medium text-sm transition-colors whitespace-nowrap"
        >
            <svg class="h-5 w-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a2 2 0 100-4H5.933l.357-1.43h8.585a1 1 0 000-2H5.933L5.707 4H17a1 1 0 000-2H3z" />
            </svg>
            Procurement Details
        </button>
    </div>

    <!-- Tab Contents -->
    <div x-show="activeTab === 'analysis'" class="space-y-6">
        <?php include __DIR__ . '/../partials/analysis-results.php'; ?>
    </div>

    <div x-show="activeTab === 'procurement'" class="space-y-6">
        <?php include __DIR__ . '/../partials/procurement-info.php'; ?>
    </div>

    <!-- Actions Footer -->
    <div class="mt-12 pt-8 border-t border-slate-200">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div class="text-sm text-slate-600">
                <p>Analysis ID: <span class="font-mono"><?php echo htmlspecialchars($result['id'] ?? 'N/A'); ?></span></p>
            </div>
            <div class="flex gap-4">
                <button
                    @click="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 font-medium text-sm transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
                <a href="/"
                   class="inline-flex items-center gap-2 px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-md font-medium text-sm transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Analysis
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($result['url']) && $result['url']): ?>
        <div class="mt-8 p-4 bg-slate-50 rounded-md border border-slate-200">
            <p class="text-sm text-slate-600">
                <span class="font-medium">Source:</span>
                <a href="<?php echo htmlspecialchars($result['url']); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 break-all">
                    <?php echo htmlspecialchars($result['url']); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
</div>
