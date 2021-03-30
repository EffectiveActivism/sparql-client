<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

class FilterExists implements ConstraintInterface
{
    protected array $patterns;

    public function __construct(array $patterns) {
        foreach ($patterns as $pattern) {
            if (!is_object($pattern) || (!($pattern instanceof ConstraintInterface) && !($pattern instanceof TripleInterface))) {
                $class = is_object($pattern) ? get_class($pattern) : gettype($pattern);
                throw new InvalidArgumentException(sprintf('Invalid constraint class: %s', $class));
            }
        }
        $this->patterns = $patterns;
    }

    public function __toString(): string
    {
        $stringValue = '';
        foreach ($this->patterns as $pattern) {
            $stringValue .= sprintf(' %s .', (string) $pattern);
        }
        return sprintf('FILTER EXISTS {%s }', $stringValue);
    }

    public function toArray(): array
    {
        $terms = [];
        foreach ($this->patterns as $pattern) {
            foreach ($pattern->toArray() as $item) {
                if ($item instanceof TermInterface) {
                    $terms[] = $item;
                }
            }
        }
        return $terms;
    }
}
