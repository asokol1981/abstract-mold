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
 * ImmutableMold — an immutable mold implementation for *safely merging and validating* entity data
 * using base data and user-provided changes, all in one centralized, finalized snapshot.
 *
 * 💡 Main idea
 * ---------------------------------------------------------------------
 * ImmutableMold acts as a single, centralized point to build and validate
 * *all* changes coming from a user (or any external input).
 * It merges base entity data (e.g., existing database values) with user changes,
 * and exposes a fully validated, read-only final structure, which can be safely stored.
 *
 * 💡 How it works
 * ---------------------------------------------------------------------
 * You initialize ImmutableMold by passing base data and changes at construction.
 * All data is merged and stored immutably — after construction, you cannot patch or reset it.
 * You can retrieve fully validated data using validated(), or get only the changed fields using changesValidated().
 * Internally, the mold automatically filters allowed fields and ignores or throws on unknown fields,
 * depending on your strictness configuration.
 *
 * 💡 Philosophy
 * ---------------------------------------------------------------------
 * ImmutableMold embodies the principle of "final snapshot".
 * Once built, it is completely frozen and safe to pass anywhere without worrying
 * about unexpected mutations or side effects.
 * It enforces explicit whitelisting of public fields via publicFields(),
 * and you define your final validated shape using validatedData().
 *
 * 💡 Why immutable?
 * ---------------------------------------------------------------------
 * The immutable design guarantees that after construction,
 * no data can be changed or patched, which eliminates potential bugs from shared mutable state.
 * All validation logic happens once, and the mold becomes a reliable, read-only data container.
 */
abstract class AbstractImmutableMold
{
    use MoldTrait;

    /**
     * Constructor: combines base data and changes.
     *
     * @param array<string, mixed> $base    Initial base data (e.g., from entity or defaults)
     * @param array<string, mixed> $changes User-provided changes (e.g., from a form or API request)
     * @param bool                 $errorIfFieldIsNotPublic Whether to throw on unknown fields
     */
    final public function __construct(array $base = [], array $changes = [], bool $errorIfFieldIsNotPublic = true)
    {
        $this->setBase($base, $errorIfFieldIsNotPublic);
        $this->setChanges($changes, $errorIfFieldIsNotPublic);
    }

    /**
     * Applies changes (patch).
     *
     * @param array<string, mixed> $changes
     * @param bool                 $errorIfFieldIsNotPublic
     */
    private function setChanges(array $changes, bool $errorIfFieldIsNotPublic): void
    {
        foreach ($changes as $key => $value) {
            $this->setChange($key, $value, $errorIfFieldIsNotPublic);
        }
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
