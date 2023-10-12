<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\Uid\Uuid;

class ApiToken
{
    public static function generate(): string
    {
        $uuid = Uuid::v4();

        return $uuid->toRfc4122();
    }
}
