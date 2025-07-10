
# AbstractMold

[![tests](https://github.com/asokol1981/abstract-mold/workflows/tests/badge.svg)](https://github.com/asokol1981/abstract-mold/actions) [![codecov](https://codecov.io/gh/asokol1981/abstract-mold/branch/main/graph/badge.svg)](https://codecov.io/gh/asokol1981/abstract-mold) [![downloads](https://img.shields.io/packagist/dt/asokol1981/abstract-mold.svg)](https://packagist.org/packages/asokol1981/abstract-mold)

💎 **AbstractMold** is a set of abstract classes and shared utilities for centralized management of entity data changes, with strict control and single-point validation.

---

## ✨ Main idea

A **Mold** acts as a single entry point for handling any incoming user data (creation, updates, or partial patches).
All changes go through the mold to ensure:

- Whitelisted allowed fields only.
- Centralized validation and normalization logic.
- Predictable and consistent data preparation.

---

## 🧩 Two mold types

### 🟢 AbstractMutableMold

Allows step-by-step incremental data changes and gradual accumulation of modifications.

- Perfect for form scenarios or multi-step wizards.
- You can modify fields one by one or in batches.
- Tracks which fields were explicitly changed.

---

### 🔵 AbstractImmutableMold

Accepts **base data** and a **patch** all at once in the constructor.

- Ideal for APIs or services where you receive all data as a single payload.
- Does not allow further modification after creation (immutable approach).

---

## 🚀 Installation

```bash
composer require asokol1981/abstract-mold
```

---

## 🚀 Usage examples

### 🟢 AbstractMutableMold usage

```php
use ASokol1981\AbstractMold\AbstractMutableMold;

final class UserMutableMold extends AbstractMutableMold
{
    protected function publicFields(): array
    {
        return ['name', 'email', 'age'];
    }

    protected function validatedData(): array
    {
        $age = $this->getRawData('age');

        return [
            'name' => (string) $this->getRawData('name', ''),
            'email' => strtolower((string) $this->getRawData('email', '')),
            'age' => $age !== null ? (int) $age : null,
        ];
    }
}

// Usage

$mold = new UserMutableMold(['name' => 'John', 'email' => 'John@EXAMPLE.COM']);
$mold->change('name', 'Johnny');
$mold->changes(['email' => 'Johnny@EXAMPLE.COM', 'age' => '25']);

$data = $mold->validated();
// ['name' => 'Johnny', 'email' => 'johnny@example.com', 'age' => 25]

$changes = $mold->changesValidated();
// ['name' => 'Johnny', 'email' => 'johnny@example.com', 'age' => 25]
```

---

### 🔵 AbstractImmutableMold usage

```php
use ASokol1981\AbstractMold\AbstractImmutableMold;

final class UserImmutableMold extends AbstractImmutableMold
{
    protected function publicFields(): array
    {
        return ['name', 'email', 'age'];
    }

    protected function validatedData(): array
    {
        $age = $this->getRawData('age');

        return [
            'name' => (string) $this->getRawData('name', ''),
            'email' => strtolower((string) $this->getRawData('email', '')),
            'age' => $age !== null ? (int) $age : null,
        ];
    }
}

// Usage

$mold = new UserImmutableMold(
    ['name' => 'John', 'email' => 'John@EXAMPLE.COM'],
    ['name' => 'Johnny', 'age' => '25']
);

$data = $mold->validated();
// ['name' => 'Johnny', 'email' => 'john@example.com', 'age' => 25]

$changes = $mold->changesValidated();
// ['name' => 'Johnny', 'age' => 25]
```

---

## ✅ Key advantages

- 🚦 Strict allowed fields control.
- 💾 Centralized validation logic.
- 🧩 Flexible design for different scenarios (mutable vs immutable).
- ⚡️ Built-in validated data cache for performance.
- 🛡 Safe and predictable API.

---

## 🤝 License

MIT © Aleksei Sokolov

---

Created in collaboration with ChatGPT (OpenAI), July 2025.
