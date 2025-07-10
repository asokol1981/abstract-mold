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

use ASokol1981\AbstractMold\AbstractMutableMold;
use PHPUnit\Framework\TestCase;

final class AbstractMutableMoldTest extends TestCase
{
    private function getTestMold(array $base = [], bool $errorIfFieldIsNotPublic = true): AbstractMutableMold
    {
        return new class($base, $errorIfFieldIsNotPublic) extends AbstractMutableMold {
            protected function publicFields(): array
            {
                return ['name', 'email', 'age', 'fourth_field'];
            }

            protected function validatedData(): array
            {
                $age = $this->getRawData('age');

                if (count($this->getRawData()) > 3) {
                    throw new \LogicException('Too many fields');
                }

                if (null !== $age && !is_numeric($age)) {
                    throw new \InvalidArgumentException('Age must be numeric');
                }

                return [
                    'name' => (string) $this->getRawData('name', ''),
                    'email' => strtolower((string) $this->getRawData('email', '')),
                    'age' => null !== $age ? (int) $age : null,
                ];
            }
        };
    }

    public function testValidated()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);

        $this->assertSame([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
        $this->assertSame('John', $mold->validated('name'));
        $this->assertSame('john@example.com', $mold->validated('email'));
        $this->assertSame(30, $mold->validated('age'));
        $this->assertSame(null, $mold->validated('unknown'));
        $this->assertSame('default', $mold->validated('unknown', 'default'));
    }

    public function testChange()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->change('name', 'Johnny');

        $this->assertSame(['name' => 'Johnny'], $mold->changesValidated());
        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testChanges()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->changes([
            'name' => 'Johnny',
            'email' => 'Johnny@EXAMPLE.COM',
        ]);

        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'johnny@example.com',
        ], $mold->changesValidated());
        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'johnny@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testChangesValidated()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->change('name', 'Johnny');

        $this->assertSame([
            'name' => 'Johnny',
        ], $mold->changesValidated());
        $this->assertSame('Johnny', $mold->changesValidated('name'));
        $this->assertSame(null, $mold->changesValidated('unknown'));
        $this->assertSame('default', $mold->changesValidated('unknown', 'default'));
    }

    public function testUnknownChangeThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mold = $this->getTestMold();
        $mold->change('unknown', 'value');
    }

    public function testUnknownChangesThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mold = $this->getTestMold();
        $mold->changes([
            'unknown1' => 'value',
            'unknown2' => 'value',
        ]);
    }

    public function testUnknownBaseFieldThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getTestMold(['unknown' => 'value']);
    }

    public function testUnknownBaseFieldsThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mold = $this->getTestMold([
            'unknown1' => 'value',
            'unknown2' => 'value',
        ]);
    }

    public function testUnknownChangeCanBeSkipped()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->change('unknown', 'value', false);

        $this->assertSame([], $mold->changesValidated());
        $this->assertSame([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testUnknownChangesCanBeSkipped()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30']);
        $mold->changes([
            'unknown1' => 'value',
            'unknown2' => 'value',
        ], false);

        $this->assertSame([], $mold->changesValidated());
        $this->assertSame([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testUnknownFieldCanBeSkipped()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30', 'unknown' => 'value'], false);
        $mold->change('unknown', 'value', false);

        $this->assertSame([], $mold->changesValidated());
        $this->assertSame([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testUnknownFieldsCanBeSkipped()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30', 'unknown1' => 'value', 'unknown2' => 'value'], false);
        $mold->changes([
            'unknown1' => 'value',
            'unknown2' => 'value',
        ], false);

        $this->assertSame([], $mold->changesValidated());
        $this->assertSame([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testValidationFails()
    {
        $mold = $this->getTestMold(['name' => 'John', 'email' => 'john@example.com', 'age' => 'oops']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Age must be numeric');

        $mold->validated();
    }

    public function testValidatedTwice()
    {
        $mold = $this->getTestMold(['name' => 'John']);
        $first = $mold->validated();
        $second = $mold->validated();

        $this->assertSame($first, $second);
    }

    public function testChangesValidatedTwice()
    {
        $mold = $this->getTestMold(['name' => 'John']);
        $first = $mold->changesValidated();
        $second = $mold->changesValidated();

        $this->assertSame($first, $second);
    }
}
