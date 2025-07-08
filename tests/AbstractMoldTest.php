<?php

declare(strict_types=1);

/*
 * This file is part of AbstractMold.
 *
 * (c) Aleksei Sokolov <asokol.beststudio@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ASokol1981\AbstractMold\Tests;

use ASokol1981\AbstractMold\AbstractMold;
use PHPUnit\Framework\TestCase;

final class AbstractMoldTest extends TestCase
{
    private function getTestMold(array $initial = []): AbstractMold
    {
        return new class($initial) extends AbstractMold {
            protected function publicFields(): array
            {
                return ['name', 'email', 'age', 'fourth_field'];
            }

            protected function validated(): array
            {
                $age = $this->get('age');

                if (count($this->get()) > 3) {
                    throw new \LogicException('Too many fields');
                }

                if (null !== $age && !is_numeric($age)) {
                    throw new \InvalidArgumentException('Age must be numeric');
                }

                return [
                    'name' => (string) $this->get('name', ''),
                    'email' => strtolower((string) $this->get('email', '')),
                    'age' => null !== $age ? (int) $age : null,
                ];
            }
        };
    }

    public function testAllValidated()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);

        $validated = $mold->allValidated();
        $this->assertIsString($validated['name']);
        $this->assertSame('John', $validated['name']);
        $this->assertIsString($validated['email']);
        $this->assertSame('john@example.com', $validated['email']);
        $this->assertIsInt($validated['age']);
        $this->assertSame(30, $validated['age']);
    }

    public function testValidatedPatch()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->applyPatch(['name' => 'Johnny']);

        $patch = $mold->validatedPatch();

        $this->assertCount(1, $patch);
        $this->assertArrayHasKey('name', $patch);
        $this->assertSame('Johnny', $patch['name']);
    }

    public function testUnknownFieldGetSkipped()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->applyPatch(['unknown_field' => 'value']);

        $patch = $mold->validatedPatch();

        $this->assertCount(0, $patch);
    }

    public function testStrictThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mold = $this->getTestMold();
        $mold->applyPatch(['unknown_field' => 'value'], true);
    }

    public function testValidationFails()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'john@example.com', 'age' => 'oops']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Age must be numeric');

        $mold->allValidated();
    }

    public function testResetClearsData()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'john@example.com', 'age' => 42]);
        $mold->reset();

        $validated = $mold->allValidated();
        $this->assertSame('', $validated['name']);
        $this->assertSame('', $validated['email']);
        $this->assertNull($validated['age']);
    }

    public function testFourthFieldThrowsException()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'john@example.com', 'age' => 42]);
        $mold->applyPatch(['fourth_field' => 'value']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Too many fields');

        $mold->validatedPatch();
    }
}
