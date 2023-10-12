<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Dates;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class DatesTest extends TestCase
{
    public function testDates(): void
    {
        $dateTime = new \DateTime('1 hour 1 minute ago');
        $text = Dates::createFuzzyDate($dateTime);
        $this->assertSame('1 hour ago', $text);

        $dateTime = new \DateTime('+1 day 1 hour');
        $text = Dates::createFuzzyDate($dateTime);
        $this->assertSame('1 day to go', $text);
    }
}
