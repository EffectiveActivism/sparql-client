<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

class UpdateResult implements UpdateResultInterface
{
    public function __construct(
        private readonly int $statusCode,
        private readonly string $body,
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
