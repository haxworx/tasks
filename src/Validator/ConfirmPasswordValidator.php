<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ConfirmPasswordValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ConfirmPassword) {
            throw new UnexpectedTypeException($constraint, ConfirmPassword::class);
        }

        $password = $this->context->getRoot()->get('plainPassword')->getData();

        if ($password !== $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;
        }
    }
}
