<?php

declare(strict_types=1);

namespace App\Twig;

use App\Utils\Dates;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TextExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('ellipsis', [$this, 'ellipsis']),
            new TwigFilter('fuzzy_date', [$this, 'fuzzyDate']),
        ];
    }

    public function ellipsis(?string $text): string
    {
        $length = strlen($text);
        if ($length >= 32) {
            return sprintf('%s...', substr($text, 0, 32));
        }

        return $text;
    }

    public function fuzzyDate(\DateTimeInterface $dateTime): string
    {
        return Dates::createFuzzyDate($dateTime);
    }
}
