<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Validation;

use EffectiveActivism\SparQlClient\Validation\ValidationResult;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValidationResultTest extends KernelTestCase
{
    public function testValidationResult()
    {
        $status = true;
        $messages = [
            'Lorem ipsum',
        ];
        $result = new ValidationResult($status, $messages);
        $this->assertEquals($status, $result->getStatus());
        $this->assertEquals($messages, $result->getMessages());
    }

    public function testValidationResultException()
    {
        $this->expectException(InvalidArgumentException::class);
        new ValidationResult(true, [0]);
    }
}
