<?php

declare(strict_types=1);

namespace MezzioSecurity\Exception;

use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class FailedLoginException extends \InvalidArgumentException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    /**
     * @param string $message
     * @param array<array-key, mixed> $errors
     */
    public function __construct(string $message = "", array $errors = [])
    {
        $this->status = 400;
        $this->detail = $message;
        $this->title = 'Failed login';
        $this->type = static::class;
        $this->additional = $errors;
        parent::__construct($message);
    }
}