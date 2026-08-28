<?php
declare(strict_types=1);
require_once __DIR__ . '/../lib/boot.php';
require_once __DIR__ . '/../lib/core-features.php';
require_once __DIR__ . '/../lib/ai.php';

function coreFeatureExpect(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }

$legacy = ['ai' => ['enabled' => true]];
coreFeatureExpect(coreFeatureEnabled($legacy, 'assistant'), 'a legacy installation did not preserve the assistant feature');
$disabled = ['core_features' => ['assistant' => false, 'peers' => false, 'marketplace' => false, 'terminal' => false], 'ai' => ['enabled' => true]];
coreFeatureExpect(!coreFeatureEnabled($disabled, 'assistant') && !aiSettings($disabled)['enabled'],
    'disabled assistant core feature still enabled assistant operations');
$status = coreFeatureStatus($disabled);
coreFeatureExpect($status[0]['id'] === 'assistant' && !$status[0]['enabled'] && in_array('sys-ai', $status[0]['dashboard_routes'], true)
    && $status[1]['id'] === 'peers' && !$status[1]['enabled'] && in_array('sys-peers', $status[1]['dashboard_routes'], true)
    && $status[2]['id'] === 'marketplace' && !$status[2]['enabled'] && in_array('sys-marketplace', $status[2]['dashboard_routes'], true)
    && $status[3]['id'] === 'terminal' && !$status[3]['enabled'] && in_array('sys-terminal', $status[3]['dashboard_routes'], true),
    'core feature status does not expose its disabled dashboard boundary');
coreFeatureExpect(coreFeatureForAction('ai-start') === 'assistant' && coreFeatureForAction('peer-test') === 'peers'
    && coreFeatureForAction('marketplace-install') === 'marketplace'
    && coreFeatureForAction('terminal-run') === 'terminal'
    && coreFeatureForAction('overview') === null, 'core feature API action mapping is incomplete or overbroad');
try { requireCoreFeatureAccess($disabled, 'ai-start'); throw new RuntimeException('disabled assistant route was accepted'); }
catch (ActionRefused $error) { coreFeatureExpect($error->getMessage() === 'this core feature is disabled', 'disabled assistant route used an unsafe refusal'); }
try { requireCoreFeatureAccess($disabled, 'peer-test'); throw new RuntimeException('disabled peer route was accepted'); }
catch (ActionRefused $error) { coreFeatureExpect($error->getMessage() === 'this core feature is disabled', 'disabled peer route used an unsafe refusal'); }
try { requireCoreFeatureAccess($disabled, 'marketplace-install'); throw new RuntimeException('disabled marketplace route was accepted'); }
catch (ActionRefused $error) { coreFeatureExpect($error->getMessage() === 'this core feature is disabled', 'disabled marketplace route used an unsafe refusal'); }
try { requireCoreFeatureAccess($disabled, 'terminal-run'); throw new RuntimeException('disabled terminal route was accepted'); }
catch (ActionRefused $error) { coreFeatureExpect($error->getMessage() === 'this core feature is disabled', 'disabled terminal route used an unsafe refusal'); }
requireCoreFeatureAccess($legacy, 'ai-start');
$api = file_get_contents(__DIR__ . '/../api.php');
$dashboard = file_get_contents(__DIR__ . '/../index.js');
coreFeatureExpect(is_string($api) && str_contains($api, "requireCoreFeatureAccess(\$config, \$action)")
    && str_contains($api, "'core_features' => coreFeatureStatus(\$config)"),
    'core feature boundary is not wired into the API shell');
coreFeatureExpect(is_string($dashboard) && str_contains($dashboard, 'applyCoreFeatureVisibility(overview.core_features)')
    && str_contains($dashboard, 'disabledCoreRoutes.has(view)'),
    'core feature boundary does not suppress disabled dashboard routes');
foreach (['peer-push.php', 'peer-data.php', 'peer-connect.php'] as $endpoint) {
    $source = file_get_contents(__DIR__ . '/../' . $endpoint);
    coreFeatureExpect(is_string($source) && str_contains($source, "coreFeatureEnabled(\$config, 'peers')")
        && str_contains($source, 'http_response_code(404)'), 'disabled peer feature leaves an external endpoint active: ' . $endpoint);
}
$airun = file_get_contents(__DIR__ . '/../lib/airun.php');
coreFeatureExpect(is_string($airun) && substr_count($airun, "coreFeatureEnabled(\$config, 'terminal')") >= 3,
    'disabled terminal feature leaves assistant terminal tools active');
echo "core feature checks passed\n";
