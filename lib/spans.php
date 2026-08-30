<?php
declare(strict_types=1);

/*
 * Lengths of time, said the way a person would say them.
 *
 * "3411 days" is arithmetically correct and completely unreadable. Nobody
 * carries a sense of how long three thousand days is; everybody knows what
 * nine years is. The rules are the ones people use out loud:
 *
 *   never more than two parts      "9y 4m", not "9y 4m 2w 3d". The third part
 *                                  is always noise - if you care about the day,
 *                                  you are not reading a span, you are reading
 *                                  a date.
 *   the units follow the size      years and months for a decade, weeks and
 *                                  days for a fortnight, hours and minutes for
 *                                  an afternoon. A span under two months in
 *                                  months is unreadable in the other direction.
 *   a zero second part is dropped  "9y", not "9y 0m".
 *
 * Months and years here are the average kind - 30.44 and 365.25 days. That is
 * wrong for any particular span and right for describing one: "9y 4m" is not a
 * claim about which day, and computing it from a calendar would not make the
 * phrase any truer.
 */

const SPAN_MINUTE = 60;
const SPAN_HOUR = 3600;
const SPAN_DAY = 86400;
const SPAN_WEEK = 604800;
const SPAN_MONTH = 2629746;
const SPAN_YEAR = 31556952;

/**
 * A number of seconds as at most two parts.
 *
 * Negative spans are the caller's business - a domain that expired is not the
 * same fact as one that expires - so this takes the size and leaves the
 * direction to whoever is writing the sentence.
 */
function plainSpan(int|float $seconds, bool $long = false): string
{
    $seconds = (int) round(abs($seconds));

    if ($seconds < 1) {
        return $long ? 'no time at all' : '0s';
    }

    /*
     * Each rung: the threshold at which it applies, its unit, and the unit
     * below it that fills the second slot.
     *
     * Two months is where months start reading better than weeks, which is the
     * one boundary that is a judgement rather than arithmetic.
     */
    $ladder = [
        [SPAN_YEAR, SPAN_YEAR, SPAN_MONTH, 'y', 'm', 'year', 'month'],
        [2 * SPAN_MONTH, SPAN_MONTH, SPAN_DAY, 'm', 'd', 'month', 'day'],
        [SPAN_WEEK, SPAN_WEEK, SPAN_DAY, 'w', 'd', 'week', 'day'],
        [SPAN_DAY, SPAN_DAY, SPAN_HOUR, 'd', 'h', 'day', 'hour'],
        [SPAN_HOUR, SPAN_HOUR, SPAN_MINUTE, 'h', 'm', 'hour', 'minute'],
        [SPAN_MINUTE, SPAN_MINUTE, 1, 'm', 's', 'minute', 'second'],
    ];

    foreach (
        $ladder
        as [
            $threshold,
            $big,
            $small,
            $bigShort,
            $smallShort,
            $bigLong,
            $smallLong,
        ]
    ) {
        if ($seconds < $threshold) {
            continue;
        }

        $whole = intdiv($seconds, $big);
        $rest = intdiv($seconds % $big, $small);

        if (!$long) {
            return $rest > 0
                ? sprintf('%d%s %d%s', $whole, $bigShort, $rest, $smallShort)
                : sprintf('%d%s', $whole, $bigShort);
        }

        $said = $whole . ' ' . $bigLong . ($whole === 1 ? '' : 's');
        return $rest > 0
            ? $said . ' ' . $rest . ' ' . $smallLong . ($rest === 1 ? '' : 's')
            : $said;
    }

    return $long
        ? $seconds . ' second' . ($seconds === 1 ? '' : 's')
        : $seconds . 's';
}

/**
 * The same span, with the direction said out loud.
 *
 * "in 9y 4m" and "9y 4m ago" are different facts and the minus sign that
 * distinguishes them is the easiest thing in a table to miss.
 */
function plainWhen(int|float $seconds, bool $long = false): string
{
    /*
     * This takes a DURATION, not a moment, and the difference is easy to get
     * wrong at the call site - both are an integer number of seconds.
     *
     * Four places passed an epoch, which came out as "in 56y 7m" because that
     * is genuinely how far 1787700000 seconds is. Anything beyond a decade is
     * either that mistake or a number nobody wanted phrased this way, so it
     * says so rather than printing something confidently absurd.
     */
    if (abs($seconds) > 10 * SPAN_YEAR) {
        return sprintf(
            '(a span of %s - if this was meant to be a date, it needs time() subtracting)',
            plainSpan($seconds, $long),
        );
    }

    if (abs($seconds) < 60) {
        return 'now';
    }

    return $seconds > 0
        ? 'in ' . plainSpan($seconds, $long)
        : plainSpan($seconds, $long) . ' ago';
}
