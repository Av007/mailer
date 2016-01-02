<?php

namespace Mailer\Validator\Constraints;

use Mailer\Service\Config;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsConfigValidator extends ConstraintValidator
{
    /**
     * @param \Mailer\Entity\Config $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        $configService = new Config();

        if ($configService->hasError()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
