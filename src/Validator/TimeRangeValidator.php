<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TimeRangeValidator extends ConstraintValidator
{
    public function validate(mixed $timeStart, Constraint $constraint): void
    {
        if (!$constraint instanceof TimeRange) {
            throw new UnexpectedTypeException($constraint, TimeRange::class);
        }

        $timeFinish = $this->context->getRoot()->get('timeFinish')->getData();

        if ($timeStart > $timeFinish) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', sprintf(
                    '(%s) and (%s)',
                    $timeStart->format('Y-m-d H:i'),
                    $timeFinish->format('Y-m-d H:i')
                ))
                ->addViolation()
            ;
        }
    }
}
