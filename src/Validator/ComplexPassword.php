<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ComplexPassword extends Constraint
{
    public $message = 'Password must contain at least one upper case character and one number.';
}
