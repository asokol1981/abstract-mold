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

use ASokol1981\AbstractMold\AbstractImmutableMold;
use PHPUnit\Framework\TestCase;

final class AbstractImmutableMoldTest extends TestCase
{
    private function getTestMold(array $base = [], array $changes = [], bool $errorIfFieldIsNotPublic = true): AbstractImmutableMold
    {
        return new class($base, $changes, $errorIfFieldIsNotPublic) extends AbstractImmutableMold {
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

    public function testCreate()
    {
        $mold = $this->getTestMold(
            [],
            ['name' => 'Johnny', 'email' => 'Johnny@EXAMPLE.COM', 'age' => '30']
        );

        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'johnny@example.com',
            'age' => 30,
        ], $mold->changesValidated());
        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'johnny@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testUpdate()
    {
        $mold = $this->getTestMold(
            ['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30'],
            ['name' => 'Johnny', 'email' => 'Johnny@EXAMPLE.COM']
        );

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

    public function testPartialUpdate()
    {
        $mold = $this->getTestMold(
            ['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30'],
            ['name' => 'Johnny']
        );

        $this->assertSame([
            'name' => 'Johnny',
        ], $mold->changesValidated());
        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testUnknownBaseFieldThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getTestMold(['unknown' => 'value']);
    }

    public function testUnknownBaseFieldsThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getTestMold([
            'unknown1' => 'value',
            'unknown2' => 'value',
        ]);
    }

    public function testUnknownChangeThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getTestMold([], ['unknown' => 'value']);
    }

    public function testUnknownChangesThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getTestMold([], [
            'unknown1' => 'value',
            'unknown2' => 'value',
        ]);
    }

    public function testUnknownFieldCanBeSkipped()
    {
        $mold = $this->getTestMold(
            ['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30', 'unknown' => 'value'],
            ['name' => 'Johnny', 'unknown' => 'value'],
            false
        );

        $this->assertSame([
            'name' => 'Johnny',
        ], $mold->changesValidated());
        $this->assertSame([
            'name' => 'Johnny',
            'email' => 'john@example.com',
            'age' => 30,
        ], $mold->validated());
    }

    public function testUnknownFieldsCanBeSkipped()
    {
        $mold = $this->getTestMold(
            ['name' => 'John', 'email' => 'John@EXAMPLE.COM', 'age' => '30', 'unknown1' => 'value', 'unknown2' => 'value'],
            ['name' => 'Johnny', 'unknown1' => 'value', 'unknown2' => 'value'],
            false
        );

        $this->assertSame([
            'name' => 'Johnny',
        ], $mold->changesValidated());
        $this->assertSame([
            'name' => 'Johnny',
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
        $mold = $this->getTestMold(['name' => 'John'], ['name' => 'Johnny']);
        $first = $mold->validated();
        $second = $mold->validated();

        $this->assertSame($first, $second);
    }

    public function testChangesValidatedTwice()
    {
        $mold = $this->getTestMold(['name' => 'John'], ['name' => 'Johnny']);
        $first = $mold->changesValidated();
        $second = $mold->changesValidated();

        $this->assertSame($first, $second);
    }
}
