<?php

/*
 * This file is part of the PhpMob package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PhpMob\ReCaptchaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class IsValid extends Constraint
{
    /**
     * The reCAPTCHA validation message
     */
    public $message = 'phpmob.ui.recaptcha_not_validated';
    public $missingInputSecretMessage = 'The secret parameter is missing.';
    public $invalidInputSecretMessage = 'The secret parameter is invalid or malformed.';
    public $missingInputResponseMessage = 'The response parameter is missing.';
    public $invalidInputResponseMessage = 'The response parameter is invalid or malformed.';
    public $badRequestMessage = 'The request is invalid or malformed.';
    public $invalidHostMessage = "Invalid host.";

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }

    /**
     * @param $code
     *
     * @return string
     */
    public function getErrorMessage($code)
    {
        switch ($code) {
            case 'missing-input-secret':
                return $this->missingInputSecretMessage;
            case 'invalid-input-secret':
                return $this->invalidInputSecretMessage;
            case 'missing-input-response':
                return $this->missingInputResponseMessage;
            case 'invalid-input-response':
                return $this->invalidInputResponseMessage;
            case 'bad-request':
                return $this->badRequestMessage;
            default:
                return $this->message;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'phpmob.recaptcha.validator';
    }
}
