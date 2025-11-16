<?php $analysis = isset($analysis) ? $analysis : []; ?>

<div class="space-y-6">
    <!-- Summary Section -->
    <?php if (isset($analysis['summary'])): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                    </svg>
                    Executive Summary
                </h3>
                <button
                    type="button"
                    @click="
                        navigator.clipboard.writeText($el.dataset.content).then(() => {
                            const originalText = $el.textContent;
                            $el.textContent = 'Copied!';
                            $el.classList.add('bg-green-600', 'hover:bg-green-700');
                            setTimeout(() => {
                                $el.textContent = originalText;
                                $el.classList.remove('bg-green-600', 'hover:bg-green-700');
                                $el.classList.add('bg-slate-600', 'hover:bg-slate-700');
                            }, 2000);
                        })
                    "
                    data-content="<?php echo htmlspecialchars($analysis['summary']); ?>"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-slate-600 hover:bg-slate-700 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy
                </button>
            </div>
            <div class="px-6 py-4">
                <p class="text-slate-700 leading-relaxed"><?php echo htmlspecialchars($analysis['summary']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Risk Analysis Section -->
    <?php if (isset($analysis['risks']) && is_array($analysis['risks']) && count($analysis['risks']) > 0): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Risk Analysis
                </h3>
                <button
                    type="button"
                    @click="
                        navigator.clipboard.writeText($el.dataset.content).then(() => {
                            const originalText = $el.textContent;
                            $el.textContent = 'Copied!';
                            $el.classList.add('bg-green-600', 'hover:bg-green-700');
                            setTimeout(() => {
                                $el.textContent = originalText;
                                $el.classList.remove('bg-green-600', 'hover:bg-green-700');
                                $el.classList.add('bg-slate-600', 'hover:bg-slate-700');
                            }, 2000);
                        })
                    "
                    data-content="<?php echo htmlspecialchars(implode(', ', $analysis['risks'])); ?>"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-slate-600 hover:bg-slate-700 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy
                </button>
            </div>
            <div class="px-6 py-4">
                <ul class="space-y-3">
                    <?php foreach ($analysis['risks'] as $risk): ?>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89a6 6 0 105.364-8.281A6.003 6.003 0 0113.477 14.89zM9 11a1 1 0 110-2 1 1 0 010 2z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-slate-700"><?php echo htmlspecialchars($risk); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recommendations Section -->
    <?php if (isset($analysis['recommendations']) && is_array($analysis['recommendations']) && count($analysis['recommendations']) > 0): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                    </svg>
                    Recommendations
                </h3>
                <button
                    type="button"
                    @click="
                        navigator.clipboard.writeText($el.dataset.content).then(() => {
                            const originalText = $el.textContent;
                            $el.textContent = 'Copied!';
                            $el.classList.add('bg-green-600', 'hover:bg-green-700');
                            setTimeout(() => {
                                $el.textContent = originalText;
                                $el.classList.remove('bg-green-600', 'hover:bg-green-700');
                                $el.classList.add('bg-slate-600', 'hover:bg-slate-700');
                            }, 2000);
                        })
                    "
                    data-content="<?php echo htmlspecialchars(implode(', ', $analysis['recommendations'])); ?>"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-slate-600 hover:bg-slate-700 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Copy
                </button>
            </div>
            <div class="px-6 py-4">
                <ul class="space-y-3">
                    <?php foreach ($analysis['recommendations'] as $recommendation): ?>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 10 10.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-slate-700"><?php echo htmlspecialchars($recommendation); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Score Section -->
    <?php if (isset($analysis['score']) && is_numeric($analysis['score'])): ?>
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Analysis Score
                </h3>
            </div>
            <div class="px-6 py-6">
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-5xl font-bold text-purple-600 mb-2">
                            <?php echo number_format($analysis['score'] * 100, 1); ?>%
                        </div>
                        <p class="text-slate-600 text-sm">
                            <?php
                                $score = $analysis['score'];
                                if ($score >= 0.9) {
                                    $rating = 'Excellent - High confidence analysis';
                                } elseif ($score >= 0.7) {
                                    $rating = 'Good - Reliable insights';
                                } elseif ($score >= 0.5) {
                                    $rating = 'Fair - Consider additional review';
                                } else {
                                    $rating = 'Low - Recommend manual verification';
                                }
                                echo $rating;
                            ?>
                        </p>
                        <div class="mt-4 w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="h-full <?php echo $score >= 0.8 ? 'bg-green-500' : ($score >= 0.6 ? 'bg-yellow-500' : 'bg-red-500'); ?>"
                                style="width: <?php echo ($score * 100); ?>%"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
