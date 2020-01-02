<?php

namespace Amp\Optimizer;

use Amp\Optimizer\Error\UnknownError;
use PHPUnit\Framework\TestCase;

final class ErrorCollectionTest extends TestCase
{

    public function testAddingErrors()
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(new UnknownError('first error'));
        $errorCollection->add(new UnknownError('second error'));
        $this->assertCount(2, $errorCollection);
        $this->assertEquals(2, $errorCollection->count());
    }

    public function testCheckingForErrors()
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(new UnknownError('first error'));
        $errorCollection->add(new UnknownError('second error'));
        $this->assertTrue($errorCollection->has(UnknownError::CODE));
        $this->assertFalse($errorCollection->has('BAD_CODE'));
    }

    public function testIteratingOverErrors()
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(new UnknownError('first error'));
        $errorCollection->add(new UnknownError('second error'));
        foreach ($errorCollection as $error) {
            $this->assertInstanceOf(Error::class, $error);
            $this->assertEquals(UnknownError::CODE, $error->getCode());
        }
        $errors = iterator_to_array($errorCollection, false);
        $this->assertEquals('first error', $errors[0]->getMessage());
        $this->assertEquals('second error', $errors[1]->getMessage());
    }
}
