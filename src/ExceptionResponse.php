<?php

namespace Bolt\Extension\Bolt\Payments;

use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception response handler.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ExceptionResponse extends Response
{
    /** @var Exception */
    protected $exception;

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param Exception $exception
     *
     * @return ExceptionResponse
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;

        return $this;
    }
}
