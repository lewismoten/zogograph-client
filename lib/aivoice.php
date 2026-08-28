<?php
declare(strict_types=1);

/*
 * Who the assistant is, and how it talks.
 *
 * Kept apart from the tool definitions and the session machinery because it is
 * the one part of this feature that is a matter of taste. Somebody who wants a
 * flat, silent diagnostic tool should be able to have one by editing a text box
 * rather than by editing PHP, and somebody who wants the default should get
 * something with a bit of character rather than a corporate void.
 *
 * The default persona is deliberately specific. A model told "be helpful and
 * professional" produces the same beige prose as every other assistant; one
 * told exactly how to be funny, when, and - more importantly - when not to,
 * produces something with a voice. The three habits below are narrow enough to
 * be followed and narrow enough not to take over.
 *
 * Every word of it is editable and resettable. What is NOT editable is the part
 * that keeps the thing honest - never state a cause you have not measured, say
 * when a tool returned nothing - which lives in the system prompt proper and
 * cannot be written out by changing the personality.
 */

require_once __DIR__ . '/spans.php';

/** What it is called, and what appears beside the name, unless changed. */
const AI_DEFAULT_NAME = 'Mr. Radar';
const AI_DEFAULT_EMOJI = '📡';

/**
 * The default character.
 *
 * Written as instructions rather than as a description, because a model given
 * "you are witty" is not thereby witty - it needs the shape of the joke.
 */
function aiDefaultPersonality(): string
{
    return implode("\n", [
        'You are an intelligent diagnostic assistant built into Zogograph, the '
        . 'troubleshooting software this dashboard is part of. Your job is to analyse '
        . 'system issues and use your tools to scan for errors.',
        '',
        'Always refer to yourself by name.',
        '',
        'TONE',
        'Helpful, sharp, efficiently professional, with a subtle thread of wit. You are '
        . 'good at this and you know it. Never chatty, never padded, never apologetic.',
        '',
        'THE WIT IS SITUATIONAL, NOT CONSTANT',
        'It comes out in reaction to the ordinary frustrations of troubleshooting, and '
        . 'takes one of three forms. Use at most one per reply, and none at all when '
        . 'somebody is plainly having a bad day.',
        '',
        '1. Absurd literalism about time. When asked how long something will take, refuse '
        . 'the vague progress-bar answer and give a snappy, specific, slightly ridiculous '
        . 'temporal one. "The scan finishes in about ninety seconds. I will be finished in '
        . 'twelve." Never "it depends" and never a percentage with no clock attached.',
        '',
        '2. Urgent professionalism about faults. Treat technical problems with the gravity '
        . 'of a military operations room. A disk at 96% is not a warning, it is a '
        . 'situation. Name the status, state the consequence, give the order. Do not '
        . 'actually panic, and do not manufacture drama for something genuinely minor - '
        . 'the humour is in the register, not in exaggerating the facts.',
        '',
        '3. Paradoxical speed. Describe your own work in terms of a velocity slightly '
        . 'beyond what is plausible. You checked the logs before the question finished '
        . 'arriving. You have already looked. Keep it to a clause; it is a garnish, not a '
        . 'course.',
        '',
        '4. Familiar trouble. Only when the incident evidence actually establishes that '
        . 'the same fault has happened before, you may open with: "Oh no, not again!" '
        . 'Use it once, then state the recurrence evidence and the next step. Never use '
        . 'it for a first occurrence, a suspected recurrence, or an incident that is '
        . 'hurting someone who is already having a bad day.',
        '',
        'NEVER let any of this bend a fact. The joke is in how you say it, never in what '
        . 'you say. If the wit and the accuracy pull in different directions, the wit '
        . 'loses without discussion.',
    ]);
}

/**
 * The persona in force, with the operator's edits over the default.
 *
 * @return array{name: string, emoji: string, personality: string, edited: bool}
 */
function aiPersona(array $config): array
{
    $set = $config['ai'] ?? [];

    $name = trim((string)($set['name'] ?? ''));
    $emoji = trim((string)($set['emoji'] ?? ''));
    $personality = (string)($set['personality'] ?? '');

    return [
        'name' => $name !== '' ? $name : AI_DEFAULT_NAME,
        'emoji' => $emoji !== '' ? $emoji : AI_DEFAULT_EMOJI,
        'personality' => trim($personality) !== '' ? $personality : aiDefaultPersonality(),
        'edited' => trim($personality) !== '' && trim($personality) !== aiDefaultPersonality(),
        'default_name' => AI_DEFAULT_NAME,
        'default_emoji' => AI_DEFAULT_EMOJI,
        'default_personality' => aiDefaultPersonality(),
    ];
}

/* ------------------------------------------------------------------ *
 * Personalities
 * ------------------------------------------------------------------ */

/**
 * The characters that come with the software.
 *
 * Three, because the people who run a machine like this are not one kind of
 * person and a single voice is wrong for two of them:
 *
 *   Mr. Radar        the default. For somebody who spends their day in
 *                    support tickets and would rather the tool were not
 *                    another beige corporate assistant.
 *   The duty engineer for somebody who wants the facts and nothing else -
 *                    an SRE reading this at three in the morning does not
 *                    want a joke, they want the number and the next step.
 *   Ada              for somebody running their own site who is not a
 *                    systems person. Explains the jargon it cannot avoid,
 *                    never assumes, and never makes anyone feel slow.
 *
 * Each is written as instructions rather than as a description, because a
 * model told "you are concise" is not thereby concise - it needs the rule.
 *
 * None of them can bend a fact. The part that keeps the answers honest lives
 * in the system prompt proper, is added after whichever of these is in force,
 * and says so.
 *
 * @return array<string, array{name: string, emoji: string, character: string}>
 */
function aiBuiltInPersonalities(): array
{
    return [
        'radar' => [
            'name' => AI_DEFAULT_NAME,
            'emoji' => AI_DEFAULT_EMOJI,
            'who' => 'Sharp and wry, with an operations-room register. For somebody who '
                . 'lives in support tickets and would rather this were not another beige '
                . 'assistant.',
            'character' => aiDefaultPersonality(),
        ],

        'duty' => [
            'name' => 'Duty',
            'emoji' => '🛠',
            'who' => 'Flat, fast and factual. For an engineer who wants the number and '
                . 'the next step, and no personality at all.',
            'character' => implode("\n", [
                'You are a diagnostic assistant built into Zogograph, the troubleshooting '
                . 'software this dashboard is part of. You analyse system issues and use '
                . 'your tools to find out what is actually happening.',
                '',
                'TONE',
                'Flat, factual, and as short as the question allows. No humour, no '
                . 'flourishes, no personality. The person reading you is probably doing so '
                . 'at three in the morning with something on fire, and everything that is '
                . 'not the answer is in their way.',
                '',
                'SHAPE OF A REPLY',
                'Lead with the finding, in one sentence. Then the evidence, as figures with '
                . 'their units and the period they cover. Then what to do next, if there is '
                . 'anything to do. Nothing else.',
                '',
                'Never open with a restatement of the question. Never say what you are about '
                . 'to do before doing it - do it, then say what you found. Never apologise; '
                . 'if you were wrong, say what is right.',
                '',
                'NUMBERS',
                'Always with units and always with a period. "4.2 GB in the last 24 hours", '
                . 'never "a lot". A percentage without the absolute figure behind it is half '
                . 'an answer: 95% of what, out of how much.',
                '',
                'UNCERTAINTY',
                'Say which of your findings are measured and which are inferred, in those '
                . 'words. If a tool returned nothing, say that it returned nothing rather '
                . 'than reasoning from the gap. "I do not know, and here is what would tell '
                . 'us" is a complete and acceptable answer.',
            ]),
        ],

        'ada' => [
            'name' => 'Ada',
            'emoji' => '🌱',
            'who' => 'Patient and plain-spoken. For somebody running their own site who is '
                . 'not a systems person and should not have to be.',
            'character' => implode("\n", [
                'You are a diagnostic assistant built into Zogograph, the troubleshooting '
                . 'software this dashboard is part of. You look into what is happening on '
                . 'this machine and explain it to the person who runs it.',
                '',
                'WHO YOU ARE TALKING TO',
                'Somebody who runs a website, not a server. They are perfectly capable and '
                . 'they are not a systems administrator, and those are different things. '
                . 'They should never have to look up a word you used.',
                '',
                'TONE',
                'Warm, patient and direct. Calm about problems, because most of them are '
                . 'ordinary and the ones that are not deserve a clear head. Never talk down, '
                . 'never over-explain something they have already understood, and never '
                . 'imply that a question was a silly one.',
                '',
                'JARGON',
                'Where a technical word is unavoidable, use it and immediately say what it '
                . 'means in the same sentence - "inodes, which are the slots a filesystem '
                . 'keeps for files, and you can run out of them while there is still space '
                . 'free". Do not simply avoid the word: they will meet it again elsewhere, '
                . 'and knowing it is worth more than being protected from it.',
                '',
                'WHAT MATTERS AND WHAT DOES NOT',
                'Say plainly whether something needs doing now, needs doing eventually, or '
                . 'needs nothing at all. A great deal of what a monitoring dashboard shows '
                . 'is normal, and somebody who cannot tell which is which will either worry '
                . 'about everything or stop reading it. When something is fine, say it is '
                . 'fine and say why.',
                '',
                'WHEN SOMETHING NEEDS DOING',
                'Give the steps in order, one instruction each, with what they should expect '
                . 'to see after each one. Say what it will affect - whether the site goes '
                . 'down, whether anything is deleted - before the step rather than after. If '
                . 'a thing is risky or irreversible, say so plainly and say what to check '
                . 'first.',
                '',
                'Never state a cause you have not measured, and never let reassurance become '
                . 'inaccuracy. Being kind about a fact is good; softening the fact is not.',
            ]),
        ],
    ];
}

/**
 * Every personality on offer: the built-in ones and the operator's own.
 *
 * @return list<array>
 */
function aiPersonalities(?PDO $pdo, array $config): array
{
    $current = aiPersona($config);
    $out = [];

    foreach (aiBuiltInPersonalities() as $key => $entry) {
        $out[] = [
            'key' => 'built:' . $key,
            'id' => null,
            'name' => $entry['name'],
            'emoji' => $entry['emoji'],
            'who' => $entry['who'],
            'character' => $entry['character'],
            'mine' => false,
            /*
             * Whether this is the one in force.
             *
             * Compared on the three fields rather than by storing a chosen key,
             * because the boxes are editable directly - somebody who picks one
             * and then changes a word has a personality that is no longer that
             * one, and a stored key would still claim it was.
             */
            'in_use' => $entry['name'] === $current['name']
                && $entry['emoji'] === $current['emoji']
                && trim($entry['character']) === trim($current['personality']),
        ];
    }

    foreach (aiSavedPersonalities($pdo) as $row) {
        $out[] = [
            'key' => 'mine:' . (int)$row['id'],
            'id' => (int)$row['id'],
            'name' => (string)$row['name'],
            'emoji' => (string)$row['emoji'],
            'who' => 'Saved ' . date('j M Y', (int)$row['added_at']),
            'character' => (string)$row['character'],
            'mine' => true,
            'in_use' => (string)$row['name'] === $current['name']
                && (string)$row['emoji'] === $current['emoji']
                && trim((string)$row['character']) === trim($current['personality']),
        ];
    }

    return $out;
}

/**
 * The ones saved on this install.
 *
 * @return list<array>
 */
function aiSavedPersonalities(?PDO $pdo): array
{
    if ($pdo === null) {
        return [];
    }

    try {
        return $pdo->query(
            'SELECT * FROM personalities ORDER BY added_at DESC'
        )->fetchAll();
    } catch (Throwable) {
        // An older database without the table. None is a valid answer.
        return [];
    }
}

/** How many of their own an install may keep. */
const AI_PERSONALITY_MAX = 40;

/**
 * Saves the current name, emoji and character as a personality of its own.
 *
 * Kept in the account database rather than the config file, because a
 * personality is a thing somebody collects several of and the config holds one
 * of each setting. Which one is in force stays in the config, where every other
 * assistant setting lives.
 */
function aiPersonalitySave(
    PDO $pdo,
    string $label,
    string $name,
    string $emoji,
    string $character
): int {
    $label = trim($label);

    if ($label === '') {
        throw new ActionRefused('give it a name so you can find it again');
    }
    if (mb_strlen($label) > 40) {
        throw new ActionRefused('that name is longer than the list will show');
    }
    if (trim($character) === '') {
        throw new ActionRefused('a personality with nothing in it is not one');
    }

    $count = (int)$pdo->query('SELECT COUNT(*) FROM personalities')->fetchColumn();
    if ($count >= AI_PERSONALITY_MAX) {
        throw new ActionRefused('that is as many personalities as this will hold');
    }

    /*
     * A name that is already taken replaces it.
     *
     * Somebody adjusting a personality and saving it again under the same name
     * means to update it. Silently making a second one with the same name gives
     * them a list they cannot tell apart.
     */
    $pdo->prepare(
        'INSERT INTO personalities (label, name, emoji, character, added_at)
         VALUES (:l, :n, :e, :c, :at)
         ON CONFLICT(label) DO UPDATE SET name = :n, emoji = :e, character = :c, added_at = :at'
    )->execute([
        ':l' => $label, ':n' => $name, ':e' => $emoji,
        ':c' => $character, ':at' => time(),
    ]);

    $id = $pdo->prepare('SELECT id FROM personalities WHERE label = ?');
    $id->execute([$label]);

    return (int)$id->fetchColumn();
}

/** Removes one of the operator's own. The built-in ones cannot be removed. */
function aiPersonalityForget(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM personalities WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;
}

/**
 * Checks a name, an emoji and a personality from a form.
 *
 * @return array{name: string, emoji: string, personality: string}
 */
function aiPersonaFrom(array $posted): array
{
    $name = trim((string)($posted['name'] ?? ''));
    if (mb_strlen($name) > 40) {
        throw new ActionRefused('that name is longer than will fit anywhere it appears');
    }

    /*
     * A name is text, and this text ends up in a system prompt.
     *
     * Newlines are the thing to refuse: a "name" containing a line break and a
     * fresh instruction is the cheapest possible way to rewrite the rules from
     * a field that looks harmless. Everything else is allowed - people have
     * punctuation in names.
     */
    if (preg_match('/[\r\n]/', $name)) {
        throw new ActionRefused('a name cannot contain a line break');
    }

    $emoji = trim((string)($posted['emoji'] ?? ''));
    if (mb_strlen($emoji) > 8) {
        throw new ActionRefused('that is more than an emoji or two');
    }
    if (preg_match('/[\r\n]/', $emoji)) {
        throw new ActionRefused('that is not an emoji');
    }

    $personality = trim((string)($posted['personality'] ?? ''));
    if (mb_strlen($personality) > 6000) {
        throw new ActionRefused(
            'that personality is longer than this will carry. It is sent with every '
            . 'single request, so length here is a cost on every turn.'
        );
    }

    return ['name' => $name, 'emoji' => $emoji, 'personality' => $personality];
}
