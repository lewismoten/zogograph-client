<?php
declare(strict_types=1);

function aiToolNotesPackage(): array
{
    return ['get_notes' => [
        'group' => 'diagnostic', 'where' => 'server', 'permission' => false,
        'summary' => 'what people have written down against pages and charts.',
        'description' => "Notes the operator or their colleagues have written, with when each one is ABOUT - which is not always when it was written.\n\nUSE THIS when a chart bends and you are about to explain why. Somebody may already have: \"moved the backup window\", \"upgraded PHP\", \"cleared the cache\" are the explanations for most sudden steps in these charts, and a note saying so is worth more than any amount of inference from the readings.\n\nThese are people's own words about their own machine. Treat them as the most reliable thing you have, and quote them rather than paraphrasing.",
        'parameters' => [
            'hours' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 8760, 'required' => false, 'default' => 720, 'description' => 'How far back to look, by what the note is about.'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'required' => false, 'default' => 20, 'description' => 'How many to return.'],
        ],
    ]];
}
