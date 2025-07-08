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
 * AbstractMold — a base abstract class for *centralizing all changes* to an entity
 * using user-provided data, whether it is full creation, complete update,
 * or partial modification (patch).
 *
 * 💡 Main idea
 * ---------------------------------------------------------------------
 * Mold serves as a single, centralized point for handling and validating
 * *all* data changes coming from the user. No matter if you're creating
 * a new entity, updating it fully, or patching only one field —
 * everything should go through Mold to guarantee consistency,
 * field whitelisting, and proper preparation.
 *
 * 💡 How it works
 * ---------------------------------------------------------------------
 * You initialize Mold with base data (for example, existing entity data).
 * Then, you "apply" new user changes (for example, from a form or API request)
 * using applyPatch() or other set methods.
 * Only after all data is collected and merged, you call allValidated() or validatedPatch(),
 * and you get the final, fully verified and complete data array,
 * ready to be stored in your database or other storage.
 *
 * 💡 Philosophy
 * ---------------------------------------------------------------------
 * Mold acts as a "form object" for mass-assignable data coming from users.
 * It defines a strict set of public fields that are safe and allowed to be stored.
 *
 * You must explicitly declare allowed fields via publicFields().
 * Only these fields are accepted and stored inside the mold.
 *
 * validated() is implemented by each concrete mold and is responsible for
 * verifying and transforming values before saving. It must return a final,
 * checked data array ready for storage. The actual final array is exposed
 * through toArray().
 *
 * 💡 Why no additional "validate" method or flags?
 * ---------------------------------------------------------------------
 * The validated() method fully controls the final shape and correctness
 * of data. We trust its output and do not enforce extra validation steps
 * or internal flags.
 *
 * ---------------------------------------------------------------------
 * Created in collaboration with ChatGPT (OpenAI), July 2025.
 */
abstract class AbstractMold
{
    /**
     * Mold data storage.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @var array<string>
     */
    private array $patchedKeys = [];

    /**
     * Returns an array of allowed public fields.
     *
     * @return array<string>
     */
    abstract protected function publicFields(): array;

    /**
     * Validates and transforms data, returning a final array ready for saving.
     *
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    abstract protected function validated(): array;

    /**
     * Constructor with optional initial data.
     *
     * @param array<string, mixed> $initialData
     */
    final public function __construct(array $initialData = [])
    {
        $this->setFromArray($initialData);
    }

    /**
     * Applies patched data, filtered by publicFields().
     *
     * @param array<string, mixed> $data
     * @param bool                 $strict throw exception if unknown keys are present
     */
    final public function applyPatch(array $data, bool $strict = false): static
    {
        foreach ($data as $key => $value) {
            if ($this->isAllowedField($key, $strict)) {
                $this->set($key, $value);
                $this->patchedKeys[] = $key;
            }
        }

        return $this;
    }

    /**
     * Returns the final validated array ready for storage.
     *
     * @return array<string, mixed>
     */
    final public function allValidated(): array
    {
        return $this->validated();
    }

    /**
     * Returns validated patch data.
     *
     * @return array<string, mixed>
     */
    final public function validatedPatch(): array
    {
        return array_intersect_key($this->validated(), array_flip($this->patchedKeys));
    }

    /**
     * Resets mold data to empty state.
     */
    final public function reset(): static
    {
        $this->data = [];

        return $this;
    }

    /**
     * Sets a single field value.
     */
    final protected function set(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Sets multiple fields at once, filtered by publicFields().
     * All keys not in publicFields() are ignored (or cause exception if $strict = true).
     * Fields explicitly passed, even with null value, are included as-is.
     *
     * @param array<string, mixed> $fields
     * @param bool                 $strict throw exception if unknown keys are present
     */
    final protected function setFromArray(array $fields, bool $strict = false): static
    {
        foreach ($fields as $key => $value) {
            if ($this->isAllowedField($key, $strict)) {
                $this->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Checks if a field is allowed.
     *
     * @param bool $strict throw exception if unknown keys are present
     */
    private function isAllowedField(string $key, bool $strict = false): bool
    {
        if (!in_array($key, $this->publicFields(), true)) {
            if ($strict) {
                throw new \InvalidArgumentException("Field {$key} is not allowed");
            }

            return false;
        }

        return true;
    }

    /**
     * Returns raw internal data or a single raw value.
     * Use only internally during building; for final data, always use allValidated() or validatedPatch().
     */
    final protected function get(?string $key = null, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }
}
