<?php

namespace Mailer\Validator\Constraints;

use Mailer\Application;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsConfigValidator extends ConstraintValidator
{
    const TEST_NAME = 'testsuites.xml';

    /**
     * @param \Mailer\Entity\Config $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        $config = Application::getInstance()->getAppConfig();
        $testFile = $config['directories']['reports'] . self::TEST_NAME;
        // run and load phpunit test
        // log tests output
        shell_exec('cd ' . MAIN_PATH . '&& ' . MAIN_PATH . 'vendor/bin/phpunit --log-junit ' . $testFile . ' -c app/');

        if ($value->checkReport()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
