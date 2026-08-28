<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/boot.php';
require_once __DIR__ . '/../lib/core-features.php';
require_once __DIR__ . '/../lib/ai.php';

function coreFeatureExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }

$legacy = ['ai' => ['enabled' => true]];
coreFeatureExpect(coreFeatureEnabled($legacy, 'assistant'), 'a legacy installation did not preserve the assistant feature');
$disabled = ['core_features' => ['assistant' => false], 'ai' => ['enabled' => true]];
coreFeatureExpect(!coreFeatureEnabled($disabled, 'assistant') && !aiSettings($disabled)['enabled'],
    'disabled assistant core feature still enabled assistant operations');
$status = coreFeatureStatus($disabled);
coreFeatureExpect($status[0]['id'] === 'assistant' && !$status[0]['enabled'] && in_array('sys-ai', $status[0]['dashboard_routes'], true),
    'core feature status does not expose its disabled dashboard boundary');
coreFeatureExpect(coreFeatureForAction('ai-start') === 'assistant' && coreFeatureForAction('overview') === null,
    'assistant API action mapping is incomplete or overbroad');
try { requireCoreFeatureAccess($disabled, 'ai-start'); throw new RuntimeException('disabled assistant route was accepted'); }
catch (ActionRefused $error) { coreFeatureExpect($error->getMessage() === 'this core feature is disabled', 'disabled assistant route used an unsafe refusal'); }
requireCoreFeatureAccess($legacy, 'ai-start');
echo "core feature checks passed\n";
