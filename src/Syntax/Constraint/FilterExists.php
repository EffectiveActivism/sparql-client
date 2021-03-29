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
            if (!($pattern instanceof ConstraintInterface) && !($pattern instanceof TripleInterface)) {
                throw new InvalidArgumentException(sprintf('Invalid constraint class: %s', get_class($pattern)));
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
                elseif (
                    $item instanceof TripleInterface ||
                    $item instanceof ConstraintInterface
                ) {
                    $terms = array_merge($terms, $item->toArray());
                }
            }
        }
        return $terms;
    }
}
