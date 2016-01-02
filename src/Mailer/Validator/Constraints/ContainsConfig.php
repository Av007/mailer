<?php

namespace Mailer\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContainsConfig extends Constraint
{
    public $message = 'The configurations failure.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
