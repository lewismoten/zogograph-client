<?php
declare(strict_types=1);

/*
 * The mount table, read properly.
 *
 * /proc/mounts is the old interface and it cannot answer the questions that
 * matter on a CloudLinux box. It says a filesystem is mounted at a path; it
 * does not say whether that is the whole filesystem or a subtree of it, which
 * mount it was derived from, or whether it is read-only. On a machine where
 * CageFS bind-mounts seventy subtrees of one filesystem into a skeleton
 * directory, that is the difference between seventy mysteries and one fact.
 *
 * /proc/self/mountinfo answers all of it. Its format is fixed and documented
 * in the kernel's filesystems/proc.txt:
 *
 *   36 35 98:0 /mnt1 /mnt2 rw,noatime shared:1 - ext3 /dev/root rw,errors=continue
 *   \/ \/ \__/ \___/ \___/ \________/ \______/ \ \__/ \_______/ \______________/
 *    1  2   3    4     5       6          7    8   9      10           11
 *
 *   1  mount id            unique for this mount
 *   2  parent mount id     what it was mounted under - the tree structure
 *   3  major:minor         which device, so two mounts of one device are visible
 *   4  root within fs      "/" for a whole filesystem, a subpath for a bind
 *   5  mount point         where it appears
 *   6  mount options       ro/rw and the rest, as seen by this mount
 *   7  optional fields     propagation, zero or more, ending at the "-"
 *   8  separator           a literal "-"
 *   9  filesystem type
 *   10 source              device or, for a bind, the originating path
 *   11 super options       options on the filesystem itself
 *
 * The variable-length optional fields in slot 7 are why this needs parsing
 * rather than a column split: the position of everything after them depends on
 * how many there are, and code that assumes a fixed offset works on a laptop
 * and breaks on the shared host it was written for.
 *
 * Nothing here sums anything. Adding the sizes of mount points is the mistake
 * this file exists to prevent - seventy bind mounts of one filesystem have one
 * filesystem's worth of space between them.
 */

require_once __DIR__ . '/collect.php';

/** Where CloudLinux keeps the template every caged account sees. */
const CAGEFS_SKELETON = '/usr/share/cagefs-skeleton';

/**
 * Every mount, with its place in the tree.
 *
 * @return list<array>
 */
function readMountInfo(string $procRoot = '/proc'): array
{
    $raw = @file_get_contents($procRoot . '/self/mountinfo');
    if ($raw === false) {
        return [];
    }

    $out = [];

    foreach (preg_split('/\R/', trim($raw)) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = preg_split('/\s+/', $line);
        if (!$parts || count($parts) < 10) {
            continue;
        }

        /*
         * Find the separator rather than counting to it.
         *
         * Slot 7 holds zero or more optional fields, so the "-" is at a
         * different index on different mounts of the same machine. Searching
         * for it is the only way that survives a kernel adding a new one.
         */
        $dash = array_search('-', $parts, true);
        if ($dash === false || $dash < 6 || !isset($parts[$dash + 2])) {
            continue;
        }

        $optional = array_slice($parts, 6, $dash - 6);
        $options = unescapeMountField($parts[5]);
        $superOptions = $parts[$dash + 3] ?? '';

        $root = unescapeMountField($parts[3]);
        $point = unescapeMountField($parts[4]);

        $out[] = [
            'mount_id' => (int)$parts[0],
            'parent_id' => (int)$parts[1],
            'device' => $parts[2],
            // "/" means the whole filesystem; anything else is a subtree, which
            // is what a bind mount looks like from here.
            'fs_root' => $root,
            'mount_point' => $point,
            'options' => $options,
            'propagation' => implode(' ', $optional),
            'fs_type' => $parts[$dash + 1],
            'source' => unescapeMountField($parts[$dash + 2]),
            'super_options' => $superOptions,
            'read_only' => in_array('ro', explode(',', $options), true),
            'bind' => $root !== '/',
        ];
    }

    return $out;
}

/**
 * Undoes the octal escaping the kernel applies to paths in mountinfo.
 *
 * Space, tab, newline and backslash are written as \040 and friends, so a
 * mount point with a space in it does not turn one line into two fields. Left
 * as-is, "/mnt/my backup" would parse as two columns and shift every field
 * after it by one.
 */
function unescapeMountField(string $value): string
{
    return (string)preg_replace_callback(
        '/\\\\([0-7]{3})/',
        fn(array $m): string => chr((int)octdec($m[1])),
        $value
    );
}

/**
 * Which mounts are distinct filesystems, and which are views of another.
 *
 * The answer to "why does df list this device seventy times". Mounts sharing a
 * major:minor are one filesystem; the one showing its whole root is the real
 * mount and the rest are subtrees of it grafted elsewhere.
 *
 * @param list<array> $mounts
 * @return list<array> one row per filesystem, each carrying its other mounts
 */
function groupByFilesystem(array $mounts): array
{
    $byDevice = [];
    foreach ($mounts as $mount) {
        $byDevice[$mount['device']][] = $mount;
    }

    $out = [];
    foreach ($byDevice as $device => $group) {
        /*
         * The primary is the one mounted at the filesystem's own root.
         *
         * Where there is none - every mount of this device is a bind of a
         * subtree, which happens inside a container - the shortest mount point
         * is the closest thing to a parent and is used instead.
         */
        $primary = null;
        foreach ($group as $mount) {
            if ($mount['fs_root'] === '/') {
                $primary = $mount;
                break;
            }
        }
        if ($primary === null) {
            usort($group, fn(array $a, array $b): int =>
                strlen($a['mount_point']) <=> strlen($b['mount_point']));
            $primary = $group[0];
        }

        $others = array_values(array_filter(
            $group,
            fn(array $m): bool => $m['mount_id'] !== $primary['mount_id']
        ));

        $out[] = [
            'device' => (string)$device,
            'fs_type' => $primary['fs_type'],
            'source' => $primary['source'],
            'mount_point' => $primary['mount_point'],
            'read_only' => $primary['read_only'],
            'mounts' => count($group),
            'binds' => count(array_filter($group, fn(array $m): bool => $m['bind'])),
            'others' => $others,
        ];
    }

    usort($out, fn(array $a, array $b): int => $b['mounts'] <=> $a['mounts']);
    return $out;
}

/**
 * Everything mounted under the CageFS skeleton.
 *
 * The equivalent of `findmnt -R /usr/share/cagefs-skeleton`, computed from the
 * same data rather than by shelling out - and it answers a question worth
 * asking on any cPanel box: the skeleton is the filesystem every caged account
 * actually sees, so what is bind-mounted into it *is* what those accounts can
 * reach. A program missing from this list is a program no account can run,
 * however well installed it is on the host.
 *
 * @param list<array> $mounts
 */
function cagefsMounts(array $mounts, string $skeleton = CAGEFS_SKELETON, bool $hostView = true): array
{
    $prefix = rtrim($skeleton, '/');
    $under = [];

    foreach ($mounts as $mount) {
        $point = $mount['mount_point'];
        if ($point === $prefix || str_starts_with($point, $prefix . '/')) {
            $mount['relative'] = $point === $prefix ? '/' : substr($point, strlen($prefix));
            $under[] = $mount;
        }
    }

    usort($under, fn(array $a, array $b): int => strcmp($a['mount_point'], $b['mount_point']));

    $devices = array_unique(array_column($under, 'device'));

    $cage = looksLikeCage($mounts);
    $insideCage = !$hostView && $cage['likely'];

    return [
        'skeleton' => $prefix,
        'present' => $under !== [],
        'cage_signals' => $cage['signals'],
        'mounts' => $under,
        'count' => count($under),
        'devices' => count($devices),
        'read_only' => count(array_filter($under, fn(array $m): bool => $m['read_only'])),
        /*
         * Said as a count of filesystems, never as a sum of sizes.
         *
         * Every one of these is a subtree of a filesystem already counted
         * elsewhere. Adding their sizes would report a machine with many times
         * the disk it has, which is the specific error this whole file is
         * arranged to avoid.
         */
        'view' => $hostView ? 'host' : 'account',
        'inside_cage' => $insideCage,
        /*
         * What an empty list means depends entirely on who is looking.
         *
         * From the host, no mounts under the skeleton is real evidence that
         * CageFS is not in use. From inside an account it is evidence of
         * nothing at all: the cage's namespace shows the skeleton's *contents*
         * as /usr and /lib, and the /usr/share/cagefs-skeleton paths that
         * assemble it exist only in the host's namespace. An account can never
         * see them, however thoroughly caged it is.
         *
         * The first version of this said "either CageFS is not installed or
         * this host does not use it" in both cases, which told somebody sitting
         * inside a cage that they were not in one.
         */
        /*
         * Three different empties, and only one of them is evidence.
         *
         * No mount table at all means the question could not be asked. From
         * inside an account it means the skeleton is invisible by design. Only
         * from the host does an empty list actually mean CageFS is not here.
         */
        'note' => $mounts === []
            ? 'No mount table could be read, so nothing can be said about CageFS either way.'
            : ($under !== []
            ? sprintf(
                '%d mounts drawn from %d filesystem%s. These are views of filesystems '
                . 'counted elsewhere, not additional space.',
                count($under),
                count($devices),
                count($devices) === 1 ? '' : 's'
            )
            : ($hostView
                ? 'Nothing is mounted under the CageFS skeleton on this host, so CageFS is '
                  . 'either not installed or not in use here.'
                : ($insideCage
                    ? 'This is the account\'s own mount table, and it is showing the inside '
                      . 'of a CageFS cage - the skeleton\'s contents appear here as /usr and '
                      . '/lib rather than under /usr/share/cagefs-skeleton. The mounts that '
                      . 'assemble the cage exist only in the host\'s namespace. Run the host '
                      . 'collector as root to see them: php host-cron.php --capabilities'
                    : 'This is the account\'s own mount table. The CageFS skeleton is not '
                      . 'visible from inside an account even when CageFS is in use, so this '
                      . 'says nothing either way. Run the host collector as root to find out: '
                      . 'php host-cron.php --capabilities'))),
    ];
}

/**
 * Whether this mount table looks like the inside of a cage.
 *
 * Asked because the absence of skeleton mounts cannot answer it, and somebody
 * reading "CageFS is not installed" while sitting inside CageFS is being
 * actively misled.
 *
 * Only one signal actually separates the two sides, and finding that out cost a
 * failing test: a mount *mentioning* cagefs proves nothing, because a host with
 * CageFS installed mentions it constantly - that is where the skeleton lives.
 * A small mount table proves nothing either; plenty of minimal hosts are small.
 *
 * What only an account sees is its own root mounted from a subtree. On a host,
 * / comes from / . Inside a cage, / is assembled by binding a per-user
 * directory into place, so the root's fs_root is somebody else's subdirectory.
 * That is the test; everything else is corroboration printed alongside it.
 *
 * And one fact settles it in the other direction: skeleton mounts being visible
 * at all means this is the host, because an account can never see them.
 *
 * @return array{likely: bool, signals: list<string>}
 */
function looksLikeCage(array $mounts, string $skeleton = CAGEFS_SKELETON): array
{
    $prefix = rtrim($skeleton, '/');

    foreach ($mounts as $mount) {
        if (str_starts_with($mount['mount_point'], $prefix)) {
            // The skeleton is assembled here, so this is the host's namespace.
            return ['likely' => false, 'signals' => []];
        }
    }

    $rootFromSubtree = null;
    foreach ($mounts as $mount) {
        if ($mount['mount_point'] === '/' && $mount['fs_root'] !== '/') {
            $rootFromSubtree = $mount['fs_root'];
            break;
        }
    }

    if ($rootFromSubtree === null) {
        return ['likely' => false, 'signals' => []];
    }

    $signals = [sprintf(
        'the root here is a subtree of its filesystem (%s), which is how a cage is built',
        $rootFromSubtree
    )];

    // Corroboration only. Neither of these would be enough on its own.
    foreach ($mounts as $mount) {
        $haystack = strtolower($mount['device'] . ' ' . $mount['source'] . ' '
            . $mount['mount_point'] . ' ' . $mount['fs_root']);
        if (str_contains($haystack, 'cagefs')) {
            $signals[] = 'a mount mentions cagefs';
            break;
        }
    }

    if (count($mounts) < 12) {
        $signals[] = sprintf('only %d mounts are visible, far fewer than a whole host',
            count($mounts));
    }

    return ['likely' => true, 'signals' => $signals];
}
