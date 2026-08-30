<?php
declare(strict_types=1);

require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/access.php';

function roleExpect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = [];
roleExpect(
    accessAllowsGroups($config, ['Viewer'], 'pages', 'processes'),
    'Viewer cannot inspect supported operational evidence',
);
roleExpect(
    !accessAllowsGroups($config, ['Viewer'], 'write') &&
        !accessAllowsGroups($config, ['Viewer'], 'ai') &&
        !accessAllowsGroups($config, ['Viewer'], 'ips'),
    'Viewer was granted an operator-only capability',
);
roleExpect(
    accessAllowsGroups($config, ['Operator'], 'notes') &&
        accessAllowsGroups($config, ['Operator'], 'live') &&
        accessAllowsGroups($config, ['Operator'], 'ai') &&
        !accessAllowsGroups($config, ['Operator'], 'write'),
    'Operator policy does not preserve the intended non-administrative workflow',
);
roleExpect(
    accessAllowsGroups($config, ['Administrator'], 'write') &&
        accessAllowsGroups($config, ['Administrator'], 'pages', 'sys-ai-data'),
    'Administrator policy is not full access',
);
roleExpect(
    accessAllowsGroups($config, ['Staff'], 'ai') &&
        accessAllowsGroups($config, ['Administrators'], 'write'),
    'legacy role aliases changed behavior',
);

echo "access role checks passed\n";
