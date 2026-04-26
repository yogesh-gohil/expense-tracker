<?php

namespace App\Exceptions;

use RuntimeException;

class CopilotException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $status = 500,
        private readonly mixed $details = null,
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function details(): mixed
    {
        return $this->details;
    }
}
