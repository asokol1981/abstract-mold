
# AbstractMold

**AbstractMold** is a base abstract class for centralizing all changes to an entity using user-provided data, whether it's full creation, complete update, or partial modification (patch).

## 💡 Philosophy

AbstractMold acts as a single, centralized point for handling and validating all data changes coming from the user. You must explicitly declare allowed fields via `publicFields()`, and implement `validated()` to return the final data array ready for storage.

## 🚀 Installation

```bash
composer require asokol1981/abstract-mold
```

## ✅ Usage

```php
use ASokol1981\AbstractMold\AbstractMold;

class UserMold extends AbstractMold
{
    protected function publicFields(): array
    {
        return ['name', 'email'];
    }

    protected function validated(): array
    {
        return [
            'name' => trim((string) $this->get('name', '')),
            'email' => strtolower((string) $this->get('email', '')),
        ];
    }
}

$mold = new UserMold(['name' => 'John', 'email' => 'JOHN@EXAMPLE.COM']);
$mold->withRawData(['name' => 'Johnny']);
$data = $mold->toArray();
// $data = ['name' => 'Johnny', 'email' => 'john@example.com'];
```

## ⚖️ Philosophy Highlights

- **Single point of data transformation**: whether you create, fully update, or patch — always via Mold.
- **Strict whitelisting**: only fields declared in `publicFields()` are accepted.
- **No internal validation flags**: final data correctness is ensured by `validated()`.

## 🧪 Testing

```bash
composer install
vendor/bin/phpunit
```

## 📄 License

MIT License. See [LICENSE](LICENSE) file for details.

---

Created by Aleksei Sokolov, in collaboration with ChatGPT (OpenAI), July 2025.
