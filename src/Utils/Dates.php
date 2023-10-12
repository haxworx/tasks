<?php

declare(strict_types=1);

namespace App\Utils;

class Dates
{
    public static function createFuzzyDate(?\DateTimeInterface $dateTime): string
    {
        $out = 'n/a';

        if (null === $dateTime) {
            return $out;
        }

        $now = new \DateTime();

        $suffix = ' ago';

        $secs = $now->format('U') - $dateTime->format('U');
        if ($secs < 0) {
            $secs = $dateTime->format('U') - $now->format('U');
            $suffix = ' to go';
        }

        if ($secs < 3600) {
            $mins = round($secs / 60);
            $out = "{$mins} minute".(1 != $mins ? 's' : ''). $suffix;
        } elseif (($secs > 3600) && ($secs < 86400)) {
            $hours = round($secs / 3600);
            $out = "{$hours} hour".(1 != $hours ? 's' : ''). $suffix;
        } else {
            $days = round($secs / 86400);
            $out = "{$days} day".(1 != $days ? 's' : ''). $suffix;
        }

        return $out;
    }
}
