<?php

declare(strict_types=1);

namespace MezzioSecurity\Exception;

use Mezzio\ProblemDetails\Exception\CommonProblemDetailsExceptionTrait;
use Mezzio\ProblemDetails\Exception\ProblemDetailsExceptionInterface;

class InvalidUserDataException extends \InvalidArgumentException implements ProblemDetailsExceptionInterface
{
    use CommonProblemDetailsExceptionTrait;

    public function __construct(string $message = "", array $errors = [])
    {
        $this->status = 400;
        $this->detail = 'Invalid user data';
        $this->title = 'Invalid user data';
        $this->type = static::class;
        $this->additional = $errors;
        parent::__construct($message);
    }
}