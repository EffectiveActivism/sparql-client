<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Validation;

interface ValidationResultInterface
{
    public function __construct(bool $status, array $messages);

    public function getStatus(): bool;

    public function getMessages(): array;
}
