<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Optionally;

use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use InvalidArgumentException;

class Optionally implements OptionallyInterface
{
    /** @var PatternInterface[] */
    protected array $patterns;

    public function __construct(array $patterns)
    {
        foreach ($patterns as $pattern) {
            if (!is_object($pattern) || (!($pattern instanceof PatternInterface))) {
                $class = is_object($pattern) ? get_class($pattern) : gettype($pattern);
                throw new InvalidArgumentException(sprintf('Invalid pattern class: %s', $class));
            }
        }
        $this->patterns = $patterns;
    }

    public function toArray(): array
    {
        return $this->patterns;
    }

    public function serialize(): string
    {
        $stringValue = '';
        foreach ($this->patterns as $pattern) {
            $stringValue .= sprintf(' %s .', $pattern->serialize());
        }
        return sprintf('OPTIONAL {%s }', $stringValue);
    }
}
