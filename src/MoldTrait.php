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

trait MoldTrait
{
    /**
     * Raw (unvalidated) data.
     *
     * @var array<string, mixed>
     */
    private array $rawData = [];

    /**
     * Cached validated data.
     *
     * @var array<string, mixed>|null
     */
    private ?array $validatedCache = null;

    /**
     * Keys explicitly marked as changed (as set).
     *
     * @var array<string, true>
     */
    private array $changedFields = [];

    /**
     * Returns validated full data or a single value by key.
     *
     * @return array<string, mixed>|mixed
     */
    final public function validated(?string $key = null, mixed $default = null): mixed
    {
        if (null === $this->validatedCache) {
            $this->validatedCache = $this->validatedData();
        }

        if (null === $key) {
            return $this->validatedCache;
        }

        return $this->validatedCache[$key] ?? $default;
    }

    /**
     * Returns validated data only for explicitly changed fields or a single value by key.
     *
     * @return array<string, mixed>|mixed
     */
    final public function changesValidated(?string $key = null, mixed $default = null): mixed
    {
        $changes = array_intersect_key(
            $this->validated(),
            $this->changedFields
        );

        if (null === $key) {
            return $changes;
        }

        return $changes[$key] ?? $default;
    }

    /**
     * Internal method to set a single field value and mark it as changed.
     */
    private function setChange(string $key, mixed $value, bool $errorIfFieldIsNotPublic): void
    {
        if ($this->isPublicField($key, $errorIfFieldIsNotPublic)) {
            $this->rawData[$key] = $value;
            $this->changedFields[$key] = true;
            $this->validatedCache = null;
        }
    }

    /**
     * Fills base data (initial entity or defaults).
     *
     * @param array<string, mixed> $base
     */
    private function setBase(array $base, bool $errorIfFieldIsNotPublic): void
    {
        foreach ($base as $key => $value) {
            if ($this->isPublicField($key, $errorIfFieldIsNotPublic)) {
                $this->rawData[$key] = $value;
            }
        }
    }

    /**
     * Checks if a field is public (allowed).
     *
     * @throws \InvalidArgumentException
     */
    private function isPublicField(string $key, bool $errorIfFieldIsNotPublic): bool
    {
        if (!in_array($key, $this->publicFields(), true)) {
            if ($errorIfFieldIsNotPublic) {
                throw new \InvalidArgumentException("Field {$key} is not allowed");
            }

            return false;
        }

        return true;
    }

    /**
     * Returns raw (unvalidated) data, or a single value by key.
     *
     * @return array<string, mixed>|mixed
     */
    final protected function getRawData(?string $key = null, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->rawData;
        }

        return $this->rawData[$key] ?? $default;
    }
}
