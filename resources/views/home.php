<div class="py-12">
    <div class="max-w-3xl mx-auto">
        <!-- Introduction Section -->
        <div class="bg-white rounded-lg shadow-sm p-8 mb-8 border border-slate-200">
            <h1 class="text-4xl font-bold text-slate-900 mb-4">
                Procurement Insight Analyzer
            </h1>
            <p class="text-lg text-slate-600 mb-4">
                Harness the power of AI to analyze procurement notices and receive actionable insights in seconds.
            </p>
            <p class="text-slate-600 mb-6">
                Simply provide the URL of a procurement notice, and our system will:
            </p>
            <ul class="list-disc list-inside text-slate-600 mb-6 space-y-2">
                <li>Extract and analyze procurement metadata</li>
                <li>Identify key requirements and risk factors</li>
                <li>Recommend optimal procurement strategies</li>
                <li>Generate detailed analysis documents</li>
            </ul>
            <p class="text-sm text-slate-500">
                All analysis is performed using advanced AI algorithms and cached for performance.
            </p>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-lg shadow-sm p-8 border border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">
                Analyze a Procurement Notice
            </h2>

            <form method="POST" action="/analyze" class="space-y-6" id="analyzeForm">
                <div class="mb-4">
                    <label for="procurement_url" class="block text-sm font-medium text-slate-700 mb-1">
                        Procurement Notice URL
                        <span class="text-red-500">*</span>
                    </label>
                    
                    <input
                        type="url"
                        id="procurement_url"
                        name="procurement_url"
                        value="<?php echo isset($form['procurement_url']) ? htmlspecialchars($form['procurement_url']) : ''; ?>"
                        placeholder="https://example.com/procurement/notice/12345"
                        required
                        class="w-full px-4 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors
                            <?php echo isset($flash['errors']['procurement_url']) ? 'border-red-500 bg-red-50' : 'border-slate-300 bg-white hover:border-slate-400'; ?>"
                    />
                    
                    <p class="mt-1 text-sm text-slate-500">Enter the complete URL to a procurement notice document</p>
                    
                    <?php if (isset($flash['errors']['procurement_url'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo htmlspecialchars($flash['errors']['procurement_url']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        x-data="{ loading: false }"
                        @click="loading = true"
                        :disabled="loading"
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!loading">
                            <svg class="mr-2 h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Analyze Procurement
                        </span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Analyzing...
                        </span>
                    </button>
                    <a href="#"
                       class="inline-flex items-center justify-center px-6 py-3 border border-slate-300 text-base font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                       @click.prevent="document.getElementById('analyzeForm').reset();">
                        Clear
                    </a>
                </div>
            </form>

            <!-- Guidance Text -->
            <div class="mt-8 pt-8 border-t border-slate-200">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Need Help?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-slate-600">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-slate-900 mb-1">Valid URLs Required</p>
                            <p>Provide a direct link to a procurement notice that is accessible online.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 011 1v1h1V5a1 1 0 011-1h1V3a1 1 0 011-1h-1V1a1 1 0 01-1-1H5a1 1 0 01-1 1v1zm0 4a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H7a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-slate-900 mb-1">Results are Cached</p>
                            <p>Analyzing the same URL twice returns instant results from our cache.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H3a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1V6a1 1 0 00-1-1h3a1 1 0 000-2 2 2 0 01-2-2H4z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-slate-900 mb-1">Quick Analysis</p>
                            <p>Analysis typically completes within seconds for most procurement notices.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured Features -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-100">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">AI-Powered Analysis</h3>
                <p class="text-slate-600 text-sm">
                    Leverages advanced AI algorithms to deeply analyze procurement requirements and identify critical insights.
                </p>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 border border-green-100">
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Export Ready</h3>
                <p class="text-slate-600 text-sm">
                    Copy analysis results with a single click. All content is formatted for easy sharing and documentation.
                </p>
            </div>
        </div>
    </div>
</div>
