<?php
declare(strict_types=1);

function aiToolRememberPackage(): array
{
    return ['remember' => [
        'group' => 'memory', 'where' => 'server', 'permission' => false,
        'summary' => 'write something down for future conversations.',
        'description' => "Stores one fact so that a LATER conversation can use it. This survives the end of this session; nothing else you do here does.\n\nUSE THIS for things that will still be true next month and that cost real work to establish:\n  - which of several installed versions of something the site actually uses, when that is not the obvious one\n  - what a recurring pattern in the readings turns out to be (a nightly backup, a cron job, a neighbour on the same host)\n  - a conclusion you reached from evidence, WITH what it rested on\n  - something the operator told you about how they want to be helped\n\nDO NOT use it for:\n  - anything already visible in the dashboard - the readings, the disk sizes, the alert list. Those are looked up, not remembered, and a remembered copy is a stale copy.\n  - what is happening right now. \"CPU is at 94%\" is a reading, not a memory, and next week it will be a lie.\n  - anything you were told in confidence, and never a password, key or token. The store is refused outright if it looks like a credential, and everything in it is shown to the operator on a page.\n  - a guess. If you are not sure, do not write it down; a remembered guess reads exactly like a remembered fact six weeks later.\n\nWriting the same SUBJECT twice replaces what was there. Use that to correct yourself rather than writing a second, contradicting memory.",
        'parameters' => [
            'kind' => ['type' => 'string', 'required' => true, 'enum' => ['fact', 'preference', 'finding', 'todo'], 'description' => 'fact = something true of this machine. preference = how the operator wants to be helped. finding = a conclusion you reached and what it rested on. todo = something identified and not yet done.'],
            'subject' => ['type' => 'string', 'required' => true, 'maxLength' => 120, 'description' => 'What this memory is ABOUT, as a short phrase, in lower case. This is the key: writing the same subject again replaces the memory. "the nightly io spike", not "I found out that the nightly io spike is the backup".'],
            'body' => ['type' => 'string', 'required' => true, 'maxLength' => 1200, 'description' => 'The fact itself, in full sentences, including what it was based on. Written for a version of you that has none of this conversation - so no "as we discussed" and no pronouns pointing at things that will not be there.'],
        ],
    ]];
}
