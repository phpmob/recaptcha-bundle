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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class IsValidValidator extends ConstraintValidator
{
    /**
     * Enable reCaptcha
     *
     * @var boolean
     */
    protected $enabled;

    /**
     * Recaptcha Private Key
     *
     * @var boolean
     */
    protected $secretKey;

    /**
     * Request Stack
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * HTTP Proxy informations
     *
     * @var array
     */
    protected $proxy;

    /**
     * Enable serverside host check.
     *
     * @var boolean
     */
    protected $verifyHost;

    public function __construct(RequestStack $requestStack, $enabled, $secretKey, array $proxy, $verifyHost)
    {
        $this->enabled = $enabled;
        $this->secretKey = $secretKey;
        $this->requestStack = $requestStack;
        $this->proxy = $proxy;
        $this->verifyHost = $verifyHost;
    }

    /**
     * @param mixed $value
     * @param Constraint|IsValid $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->enabled) {
            return;
        }

        $masterRequest = $this->requestStack->getMasterRequest();
        $remoteip = $masterRequest->getClientIp();
        $answer = $masterRequest->get("g-recaptcha-response");

        if (false === $response = $this->checkAnswer($this->secretKey, $remoteip, $answer)) {
            $this->context->addViolation($constraint->message);

            return;
        };

        if (false === $response["success"]) {
            foreach ($response['error-codes'] as $code) {
                $this->context->addViolation($constraint->getErrorMessage($code));
            }

            return;
        }

        if ($this->verifyHost && $response["hostname"] !== $masterRequest->getHost()) {
            $this->context->addViolation($constraint->invalidHostMessage);
        }
    }

    /**
     * @param string $secretKey
     * @param string $remoteip
     * @param string $answer
     *
     * @throws ValidatorException When missing remote ip
     *
     * @return boolean
     */
    private function checkAnswer($secretKey, $remoteip, $answer)
    {
        if ($remoteip == null || $remoteip == "") {
            throw new ValidatorException("vihuvac_recaptcha.validator.remote_ip");
        }

        if ($answer == null || strlen($answer) == 0) {
            return false;
        }

        return json_decode($this->httpGet([
            "secret" => $secretKey,
            "remoteip" => $remoteip,
            "response" => $answer
        ]), true);
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server.
     *
     * @param array $data
     *
     * @return string response
     */
    private function httpGet($data)
    {
        return file_get_contents(sprintf(
            "https://www.google.com/recaptcha/api/siteverify?%s", http_build_query($data, null, "&")
        ), false, $this->getResourceContext());
    }

    /**
     * Resource context.
     *
     * @return resource context for HTTP Proxy.
     */
    private function getResourceContext()
    {
        if (null === $this->proxy["host"] || null === $this->proxy["port"]) {
            return null;
        }

        $options = array();
        foreach (array("http", "https") as $protocol) {
            $options[$protocol] = array(
                "method" => "GET",
                "proxy" => sprintf("tcp://%s:%s", $this->proxy["host"], $this->proxy["port"]),
                "request_fulluri" => true
            );

            if (null !== $this->proxy["auth"]) {
                $options[$protocol]["header"] = sprintf("Proxy-Authorization: Basic %s", base64_encode($this->proxy["auth"]));
            }
        }

        return stream_context_create($options);
    }
}
