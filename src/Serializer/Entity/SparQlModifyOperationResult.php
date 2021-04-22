<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Serializer\Entity;

use Symfony\Component\Serializer\Annotation\SerializedName;

class SparQlModifyOperationResult
{
    #[SerializedName('@modified')]
    protected int $modified;

    #[SerializedName('@milliseconds')]
    protected int $miliseconds;

    /**
     * Getters.
     */

    public function getMiliseconds(): int
    {
        return $this->miliseconds;
    }

    public function getModified(): int
    {
        return $this->modified;
    }

    /**
     * Setters.
     */

    public function setMiliseconds(int $miliseconds): self
    {
        $this->miliseconds = $miliseconds;
        return $this;
    }

    public function setModified(int $modified): self
    {
        $this->modified = $modified;
        return $this;
    }
}
