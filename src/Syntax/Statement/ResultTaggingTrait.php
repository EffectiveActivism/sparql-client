<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

trait ResultTaggingTrait
{
    protected bool $tagResults = true;

    public function withoutResultTags(): static
    {
        $this->tagResults = false;
        return $this;
    }

    public function tagsResults(): bool
    {
        return $this->tagResults;
    }
}
