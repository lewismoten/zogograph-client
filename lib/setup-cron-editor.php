<?php
declare(strict_types=1);

/* Reusable, presentation-only controls for Setup cron panels. Commands,
 * crontab reads, and applying changes remain exclusively in SetupCronApi. */

require_once __DIR__ . '/schedule.php';

function setupCronEditorEscape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Renders one named job's timing controls. `$prefix` is used only in form
 * names and must be supplied by the trusted Setup panel, not request input.
 */
function renderSetupCronEditor(string $prefix, string $title, array $choice, string $job = 'vitals'): string
{
    if (!preg_match('/^[a-z][a-z0-9_-]{0,62}$/', $prefix)) {
        throw new InvalidArgumentException('cron editor form prefix is invalid');
    }
    $profile = (string)($choice['profile'] ?? 'none');
    $custom = (array)($choice['custom'] ?? []);
    $out = '<fieldset><legend>' . setupCronEditorEscape($title) . '</legend><label>Profile<select name="'
        . setupCronEditorEscape($prefix) . '_profile">';
    foreach (cronEditorProfiles($job) as $id => $definition) {
        $out .= '<option value="' . setupCronEditorEscape($id) . '"' . ($profile === $id ? ' selected' : '') . '>'
            . setupCronEditorEscape((string)$definition['label']) . '</option>';
    }
    $offset = (array)($choice['offset'] ?? []);
    $out .= '</select></label><div class="row"><label>Minute offset (0–59)<input type="number" name="'
        . setupCronEditorEscape($prefix) . '_offset_minute" min="0" max="59" value="'
        . setupCronEditorEscape((string)($offset['minute'] ?? 0)) . '"></label><label>Hour offset (0–23)<input type="number" name="'
        . setupCronEditorEscape($prefix) . '_offset_hour" min="0" max="23" value="'
        . setupCronEditorEscape((string)($offset['hour'] ?? 0)) . '"></label></div><p class="help">Offsets apply to a profile; Custom cron uses the five fields below directly.</p><details><summary>Custom cron builder</summary>'
        . '<p class="help">Used only when Custom five-field cron is selected. Standard cron has minutes, hours, day of month, month, and day of week; seconds are intentionally not supported.</p><div class="row">';
    foreach (['minute' => 'Minutes (0–59)', 'hour' => 'Hours (0–23)', 'day' => 'Day of month (1–31)', 'month' => 'Month (1–12)', 'weekday' => 'Day of week (0–7)'] as $field => $label) {
        $out .= '<label>' . setupCronEditorEscape($label) . '<input type="text" name="'
            . setupCronEditorEscape($prefix . '_' . $field) . '" value="'
            . setupCronEditorEscape((string)($custom[$field] ?? '*'))
            . '" pattern="[0-9*,/\\-]+" maxlength="80" spellcheck="false"></label>';
    }
    return $out . '</div></details></fieldset>';
}
