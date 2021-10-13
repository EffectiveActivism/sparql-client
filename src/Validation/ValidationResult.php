<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Validation;

use InvalidArgumentException;

class ValidationResult implements ValidationResultInterface
{
    protected array $messages;

    protected bool $status;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(bool $status, array $messages)
    {
        $this->status = $status;
        foreach ($messages as $message) {
            if (!is_string($message)) {
                throw new InvalidArgumentException('Validation result messages must be strings');
            }
        }
        $this->messages = $messages;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
