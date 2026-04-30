<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ResultTaggableInterface
{
    /**
     * Disable per-row cache tagging for this statement. Structural tags
     * derived from the query pattern still apply; only result-derived tags
     * are skipped. Use this for aggregate or large-result queries where the
     * UUID5-per-binding cost of per-row tagging dominates wall time.
     */
    public function withoutResultTags(): static;

    public function tagsResults(): bool;
}
