<?php
use Carbon\CarbonImmutable;

$result = isset($result) ? $result : [];
$fromCache = isset($result['from_cache']) ? $result['from_cache'] : false;
$generatedAt = isset($result['generated_at']) ? CarbonImmutable::parse($result['generated_at']) : null;
?>

<div class="bg-slate-50 rounded-lg p-4 border border-slate-200 space-y-3">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-slate-700">Analysis Status:</span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
            <?php echo $fromCache ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
            <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <?php echo $fromCache ? 'From Cache' : 'Fresh Analysis'; ?>
        </span>
    </div>

    <?php if ($generatedAt): ?>
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Generated:</span>
            <span class="text-sm text-slate-600">
                <?php echo $generatedAt->format('M d, Y \a\t H:i:s'); ?>
                <span class="text-xs text-slate-500">(<?php echo $generatedAt->diffForHumans(); ?>)</span>
            </span>
        </div>
    <?php endif; ?>

    <?php if (isset($result['meta']) && is_array($result['meta'])): ?>
        <?php if (isset($result['meta']['model'])): ?>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-700">AI Model:</span>
                <span class="text-sm text-slate-600 font-mono"><?php echo htmlspecialchars($result['meta']['model']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($result['meta']['tokens_used'])): ?>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-700">Tokens Used:</span>
                <span class="text-sm text-slate-600"><?php echo htmlspecialchars($result['meta']['tokens_used']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($result['meta']['processing_time'])): ?>
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-700">Processing Time:</span>
                <span class="text-sm text-slate-600"><?php echo htmlspecialchars($result['meta']['processing_time']); ?>ms</span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="flex items-center gap-2 text-xs text-slate-500 pt-2 border-t border-slate-200">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
        </svg>
        <span>Analysis ID: <span class="font-mono"><?php echo htmlspecialchars($result['id'] ?? 'N/A'); ?></span></span>
    </div>
</div>
