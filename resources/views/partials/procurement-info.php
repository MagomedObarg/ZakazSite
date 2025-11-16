<?php $procurement = isset($procurement) ? $procurement : []; ?>

<div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-200">
        <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
            <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H3a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1V6a1 1 0 00-1-1h3a1 1 0 000-2 2 2 0 01-2-2H4z" clip-rule="evenodd" />
            </svg>
            Procurement Details
        </h3>
    </div>

    <div class="px-6 py-4 space-y-4">
        <?php if (isset($procurement['title'])): ?>
            <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-1">Title</h4>
                <p class="text-slate-900"><?php echo htmlspecialchars($procurement['title']); ?></p>
            </div>
        <?php endif; ?>

        <?php if (isset($procurement['buyer']) && is_array($procurement['buyer'])): ?>
            <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-1">Buyer</h4>
                <p class="text-slate-900"><?php echo htmlspecialchars($procurement['buyer']['name'] ?? 'N/A'); ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4">
            <?php if (isset($procurement['deadline'])): ?>
                <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-1">Deadline</h4>
                    <p class="text-slate-900"><?php echo htmlspecialchars($procurement['deadline']); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($procurement['value']) && is_array($procurement['value'])): ?>
                <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-1">Budget</h4>
                    <p class="text-slate-900">
                        <?php echo number_format($procurement['value']['amount'] ?? 0, 2); ?>
                        <?php echo htmlspecialchars($procurement['value']['currency'] ?? 'USD'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($procurement['items']) && is_array($procurement['items']) && count($procurement['items']) > 0): ?>
            <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-2">Items Required</h4>
                <div class="space-y-2">
                    <?php foreach ($procurement['items'] as $item): ?>
                        <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-md border border-slate-200">
                            <svg class="h-5 w-5 text-slate-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a2 2 0 100-4H5.933l.357-1.43h8.585a1 1 0 000-2H5.933L5.707 4H17a1 1 0 000-2H3z" />
                            </svg>
                            <div class="flex-1">
                                <p class="font-medium text-slate-900"><?php echo htmlspecialchars($item['name'] ?? 'Item'); ?></p>
                                <p class="text-xs text-slate-600">
                                    Qty: <?php echo htmlspecialchars($item['quantity'] ?? 'N/A'); ?> <?php echo htmlspecialchars($item['unit'] ?? ''); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
