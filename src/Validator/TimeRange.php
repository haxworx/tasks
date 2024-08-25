<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TimeRange extends Constraint
{
    public string $message = 'Finish time must be greater than start time.';
}
