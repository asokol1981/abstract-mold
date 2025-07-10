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

namespace ASokol1981\AbstractMold;

/**
 * AbstractMutableMold — a flexible base abstract class for *incrementally changing* entity data
 * with full control over which fields are marked as "changed" and which remain as base.
 *
 * 💡 Main idea
 * ---------------------------------------------------------------------
 * AbstractMutableMold serves as a centralized point for managing and validating
 * *mutable* entity data, whether you're building a new entity step by step,
 * performing partial updates, or mixing base data with incoming user changes.
 * It keeps track of which fields were explicitly changed, allowing you to clearly
 * separate "base" data from "modified" data.
 *
 * 💡 How it works
 * ---------------------------------------------------------------------
 * You start by optionally providing base data (e.g., existing database data or defaults).
 * Then, you progressively modify fields using change() for single fields or changes()
 * for batch updates.
 * After collecting all inputs, you call validated() to get a fully verified, ready-for-storage
 * array of all fields, or changesValidated() to get only the fields explicitly marked as changed.
 *
 * 💡 Philosophy
 * ---------------------------------------------------------------------
 * The class enforces explicit whitelisting of allowed fields via publicFields().
 * It treats each change as an intentional user-driven modification and maintains
 * a separate list of changed keys to enable partial updates (patch semantics).
 *
 * 💡 Why mutable?
 * ---------------------------------------------------------------------
 * Mutable design is useful when you want to build or adjust an entity's state
 * incrementally, possibly across different layers (e.g., from UI inputs, service defaults,
 * or API patches).
 * Unlike immutable molds, this approach allows you to evolve data step by step
 * before final validation and saving.
 */
abstract class AbstractMutableMold
{
    use MoldTrait;

    /**
     * Constructor with optional base data.
     *
     * @param array<string, mixed> $base
     * @param bool                 $errorIfFieldIsNotPublic
     */
    final public function __construct(array $base = [], bool $errorIfFieldIsNotPublic = true)
    {
        $this->setBase($base, $errorIfFieldIsNotPublic);
    }

    /**
     * Change a single field.
     *
     * @param string $key
     * @param mixed  $value
     * @param bool   $errorIfFieldIsNotPublic
     */
    final public function change(string $key, mixed $value, bool $errorIfFieldIsNotPublic = true): static
    {
        $this->setChange($key, $value, $errorIfFieldIsNotPublic);
        return $this;
    }

    /**
     * Change multiple fields at once.
     *
     * @param array<string, mixed> $changes
     * @param bool                 $errorIfFieldIsNotPublic
     */
    final public function changes(array $changes, bool $errorIfFieldIsNotPublic = true): static
    {
        foreach ($changes as $key => $value) {
            $this->change($key, $value, $errorIfFieldIsNotPublic);
        }
        return $this;
    }

    /**
     * Defines the list of public (allowed) fields.
     *
     * @return array<string>
     */
    abstract protected function publicFields(): array;

    /**
     * Must return final validated data (ready for storage).
     *
     * @return array<string, mixed>
     */
    abstract protected function validatedData(): array;
}
