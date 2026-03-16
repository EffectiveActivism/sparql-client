<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

class AskResult implements AskResultInterface
{
    public function __construct(private readonly bool $answer)
    {
    }

    public function getAnswer(): bool
    {
        return $this->answer;
    }
}
