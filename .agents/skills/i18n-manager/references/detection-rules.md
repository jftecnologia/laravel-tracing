# Detection Rules

String detection heuristics for backend and frontend i18n auditing.

---

## Table of Contents

1. [Backend (PHP)](#backend-php)
2. [Frontend (TSX/JSX)](#frontend-tsxjsx)
3. [Blade Templates](#blade-templates)
4. [False Positive Mitigation](#false-positive-mitigation)

---

## Backend (PHP)

### Must Translate

Strings in these contexts are user-facing and must use `__()`:

| Context               | Pattern                                                   | Example                                     |
| --------------------- | --------------------------------------------------------- | ------------------------------------------- |
| Flash messages        | `->with('error'\|'success'\|'warning'\|'info', 'string')` | `->with('error', __('Not found.'))`         |
| Abort messages        | `abort(404, 'string')`, `abort_if()`, `abort_unless()`    | `abort(403, __('Unauthorized.'))`           |
| Exception messages    | `throw new *Exception('string')`                          | `throw new AppException(__('Invalid.'))`    |
| Form Request messages | `messages()` return array values                          | `'name.required' => __('Name required.')`   |
| Enum labels           | `label()`, `description()`, `name()` methods              | `return __('Active');`                      |
| Notification text     | `toMail()` subject/line/action, `toArray()`               | `->subject(__('New notification'))`         |
| Validation rules      | Custom messages in `Rule::*`                              | `Rule::unique()->message(__('Exists.'))`    |
| Controller responses  | Strings returned in JSON `message` fields                 | `['message' => __('Created.')]`             |
| Redirect messages     | `->withErrors(['field' => 'string'])`                     | `->withErrors(['email' => __('Invalid.')])` |

### Must NOT Translate (Exclusions)

| Context                       | Why                               |
| ----------------------------- | --------------------------------- |
| Route names (`Route::name()`) | Technical identifiers             |
| Middleware strings            | Internal framework references     |
| Config keys, env calls        | Configuration, not UI             |
| DB column/table names         | Schema, not UI                    |
| `Log::*()` messages           | Developer-facing, not user-facing |
| Class/namespace strings       | Code references                   |
| Enum backing values           | Data, not display                 |
| Query builder strings         | SQL, not UI                       |
| Cache keys                    | Technical identifiers             |
| Event/job class names         | Internal references               |
| Test assertion messages       | Developer-facing                  |

### Detection Strategy

1. Use `grep_search` for strings in target patterns
2. Cross-reference with `__()` usage — if a file has strings in translatable contexts NOT wrapped in `__()`, flag them
3. Check Enum files — look for `return 'String';` in `label()`, `description()` methods where `__()` is missing

---

## Frontend (TSX/JSX)

### Must Translate

| Context                 | Pattern                           | Fix                             |
| ----------------------- | --------------------------------- | ------------------------------- |
| JSX text content        | `<h1>Dashboard</h1>`              | `<h1>{t('Dashboard')}</h1>`     |
| Label props             | `label="Name"`                    | `label={t('Name')}`             |
| Title props             | `title="Settings"`                | `title={t('Settings')}`         |
| Placeholder props       | `placeholder="Search..."`         | `placeholder={t('Search...')}`  |
| Description props       | `description="Enter info"`        | `description={t('Enter info')}` |
| Alt text                | `alt="Logo"`                      | `alt={t('Logo')}`               |
| Aria labels             | `aria-label="Close"`              | `aria-label={t('Close')}`       |
| Button text             | `<button>Save</button>`           | `<button>{t('Save')}</button>`  |
| Table headers           | `<th>Status</th>`                 | `<th>{t('Status')}</th>`        |
| Error messages          | String literals in error displays | Wrap with `t()`                 |
| Toast/notification text | String alerts and toasts          | Wrap with `t()`                 |

### Must NOT Translate (Exclusions)

| Context                                    | Why                     |
| ------------------------------------------ | ----------------------- |
| `className` / CSS classes                  | Styling, not content    |
| Tailwind utility classes                   | Styling tokens          |
| Route/href paths                           | Navigation, not display |
| Wayfinder references                       | Code-generated routes   |
| Component names                            | Code references         |
| `data-*` attributes                        | Technical attributes    |
| `console.log()` messages                   | Developer-facing        |
| Boolean/numeric literals                   | Not translatable        |
| Import/require paths                       | Code references         |
| `key` prop values                          | React internals         |
| Icon names (e.g., `<Icon name="check" />`) | Technical identifiers   |
| Enum values as props                       | Data, not display       |
| Type annotations                           | TypeScript, not runtime |

### Detection Strategy

1. Scan `.tsx` and `.jsx` files in `resources/js/`
2. Look for JSX text content (text between opening/closing tags)
3. Look for string literal props in translatable prop names (label, title, placeholder, description, alt, aria-label)
4. Verify import of `useLaravelReactI18n` — if missing, the entire file needs i18n setup
5. Flag components that render user-facing text without `t()` wrapping

---

## Blade Templates

### Must Translate

| Context            | Pattern                     | Fix                              |
| ------------------ | --------------------------- | -------------------------------- |
| Text content       | `<h1>Title</h1>`            | `<h1>{{ __('Title') }}</h1>`     |
| Attribute text     | `placeholder="text"`        | `placeholder="{{ __('text') }}"` |
| `@section` content | `@section('title', 'Page')` | `@section('title', __('Page'))`  |

### Must NOT Translate

| Context                      | Why                 |
| ---------------------------- | ------------------- |
| `@extends`, `@include` paths | Template references |
| `@props` directives          | Component bindings  |
| CSS classes in `class=""`    | Styling             |
| `{{ $variable }}`            | Already dynamic     |

---

## False Positive Mitigation

### General Rules

1. **Single-word technical strings** — Skip strings that look like identifiers: all-lowercase, no spaces, camelCase, snake_case, SCREAMING_CASE
2. **Short symbol strings** — Skip strings like `/`, `|`, `#`, `:`, `.`, `,`
3. **URL-like strings** — Skip strings starting with `http://`, `https://`, `/`, `mailto:`
4. **Number-only strings** — Skip `'0'`, `'100'`, etc.
5. **HTML tag strings** — Skip strings that are pure HTML tags
6. **Variable interpolation only** — Skip strings that are only `{$var}` or similar

### Confidence Levels

When reporting findings, assign confidence levels:

- **HIGH**: String is clearly user-facing text (multi-word, sentence-like, UI labels)
- **MEDIUM**: String could be user-facing (single word that could be a label or an identifier)
- **LOW**: String is ambiguous (could be technical or user-facing)

Only auto-fix HIGH confidence findings. Present MEDIUM for user review. Skip LOW unless user requests comprehensive mode.
