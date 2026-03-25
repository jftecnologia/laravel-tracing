# Translation Key Conventions

Rules for choosing translation keys in the application.

---

## Default: JSON-Key Convention

Use the **English text as the translation key**. This is the project standard for general UI strings.

```php
// Backend
__('Dashboard')
__('Project not found.')
__('Save changes')
```

```tsx
// Frontend
t("Dashboard");
t("Project not found.");
t("Save changes");
```

Translation file (`lang/pt_BR.json`):

```json
{
  "Dashboard": "Painel de controle",
  "Project not found.": "Projeto não encontrado.",
  "Save changes": "Salvar alterações"
}
```

### Benefits

- Readable code — the key IS the English fallback
- Matches Laravel's default JSON translation system
- If a key is missing, the English text displays naturally
- Consistent with existing `pt_BR.json` entries

### Guidelines

- Use proper English capitalization and punctuation
- Use sentence case for messages: `"Project created successfully."`
- Use title case for labels: `"Dashboard"`, `"Settings"`
- Include trailing periods on full sentences
- Keep keys concise — avoid overly long strings as keys

---

## Named Keys: When to Use

Use **dot-notation named keys** only in these specific cases:

### 1. Framework/package translations (already established)

```php
// These already use named keys — do not change
__('auth.failed')
__('validation.required')
__('passwords.reset')
__('pagination.previous')
```

### 2. Same English text, different context

When the same English word translates differently depending on context:

```json
{
  "status.active": "Ativo",
  "status.active_verb": "Ativar"
}
```

### 3. Very long strings

When the English text is too long to be a practical key (>100 characters):

```php
// Bad — too long as key
__('Are you sure you want to permanently delete this project? This action cannot be undone and all associated data will be lost.')

// Good — use a named key
__('projects.confirm_permanent_delete')
```

### 4. Structured groups with many related entries

When a domain has many related translations that benefit from grouping:

```php
// OK for small groups — JSON-key works fine
__('Low')
__('Medium')
__('High')
__('Critical')

// Better as named keys when there are many related items with structure
__('risk_levels.informational')
__('risk_levels.low')
__('risk_levels.medium')
__('risk_levels.high')
__('risk_levels.critical')
```

---

## File Organization

| File               | Content                                                 | Format                       |
| ------------------ | ------------------------------------------------------- | ---------------------------- |
| `lang/pt_BR.json`  | All JSON-key translations                               | `{ "English": "Português" }` |
| `lang/en.json`     | English JSON translations (optional, for explicit keys) | `{ "key": "English text" }`  |
| `lang/en/*.php`    | Laravel framework named keys                            | `return ['key' => 'value']`  |
| `lang/pt_BR/*.php` | Portuguese framework named keys                         | `return ['key' => 'valor']`  |

### Rules

- Never duplicate a key across JSON and PHP files
- Framework translations stay in PHP files
- Application translations go in JSON files
- When adding a new JSON-key translation, add to ALL locale JSON files
- When a key exists in `pt_BR.json`, the fallback locale (`en`) does not need an entry (the key IS the English text)

---

## Replacements and Pluralization

### Variable Replacements

Use Laravel's `:placeholder` syntax:

```php
__('Hello, :name!', ['name' => $user->name])
```

```tsx
t("Hello, :name!", { name: user.name });
```

### Pluralization

Use Laravel's `|` pipe syntax:

```php
trans_choice('{0} No items|{1} One item|[2,*] :count items', $count)
```

```tsx
tChoice("{0} No items|{1} One item|[2,*] :count items", count);
```

Translation:

```json
{
  "{0} No items|{1} One item|[2,*] :count items": "{0} Nenhum item|{1} Um item|[2,*] :count itens"
}
```
