<?php

namespace App\Exceptions;
use Exception;

class CustomException extends Exception
{
    protected $code;

    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const SERVER_ERROR = 500;

    public function __construct(string $message, int $code = self::NOT_FOUND)
    {
        $this->code = $code;

        parent::__construct($message, $code);
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }
}
