---
name: i18n-manager
description: Internationalization (i18n) audit, fix, and sync for Laravel + React applications. Scans PHP, TSX, and Blade files for hardcoded user-facing strings, verifies translation helper usage (__(), t()), identifies gaps and orphaned entries in translation files, replaces hardcoded strings with translation helpers, and synchronizes translation files across locales. Use when the user asks to check translations, audit i18n compliance, fix untranslated strings, sync translation files, find translation gaps, or verify i18n readiness (e.g., 'verificar traduções', 'checar i18n', 'auditar traduções', 'corrigir traduções', 'sincronizar traduções', 'check translations', 'fix translations', 'find untranslated strings', 'i18n audit', 'translation sync').
---

# i18n Manager

Audit, fix, and synchronize internationalization across Laravel backend and React frontend.

## Related References

- [references/detection-rules.md](references/detection-rules.md) — Detection patterns and exclusions for backend/frontend
- [references/key-conventions.md](references/key-conventions.md) — Translation key naming conventions
- [references/report-template.md](references/report-template.md) — Audit report template

---

## 1. Workflow Overview

```text
Phase 0: Determine Mode & Scope
  → Phase 1: Prerequisites Check
    → Phase 2: Scan & Analyze
      → Phase 3: Report / Propose Changes
        → Phase 4: Approval Gate (FIX/SYNC only)
          → Phase 5: Apply Changes (FIX/SYNC only)
            → Phase 6: Verify
```

---

## 2. Phase 0: Determine Mode & Scope

### Mode Selection

Determine the operating mode from user intent:

| User Intent                                            | Mode     |
| ------------------------------------------------------ | -------- |
| "check", "audit", "verify", "report"                   | AUDIT    |
| "fix", "correct", "replace", "update strings"          | FIX      |
| "sync", "synchronize", "find missing", "find orphaned" | SYNC     |
| Unclear                                                | Ask user |

### Scope Selection

Determine scope from context:

1. **Explicit**: User specifies files/directories → use those
2. **Contextual**: User is working on a specific feature → scope to related files
3. **Implicit**: No indication → ask user: "Deseja analisar o projeto inteiro ou um diretório/arquivo específico?"

Valid scopes:

- Full project (`app/`, `resources/js/`, `resources/views/`)
- Specific directory (e.g., `resources/js/pages/admin/`)
- Specific file (e.g., `app/Http/Controllers/Admin/ProjectController.php`)

---

## 3. Phase 1: Prerequisites Check

Before scanning, verify the project's i18n infrastructure.

### Backend Checks

1. Verify `lang/` directory exists with locale subdirectories
2. Check for JSON translation files (`lang/*.json`)
3. Note existing locales (e.g., `en`, `pt_BR`)

### Frontend Checks

1. Check if `laravel-react-i18n` is installed → `grep 'laravel-react-i18n' package.json`
2. Check if Vite plugin is configured → `grep 'laravel-react-i18n/vite' vite.config`
3. Check if `LaravelReactI18nProvider` wraps the app → `grep 'LaravelReactI18nProvider' resources/js/app.tsx`

If frontend i18n is NOT set up:

- In **AUDIT** mode: flag as critical finding, continue scanning
- In **FIX** mode: present setup steps for user approval before proceeding
    1. `npm i laravel-react-i18n`
    2. Add `i18n()` plugin to `vite.config.ts`
    3. Wrap app in `LaravelReactI18nProvider` in `app.tsx`
    4. Add `lang/php_*.json` to `.gitignore`

### Frontend i18n Setup Details

When setting up `laravel-react-i18n`, use this pattern for `app.tsx`:

```tsx
import { LaravelReactI18nProvider } from 'laravel-react-i18n';

// Inside createInertiaApp setup:
root.render(
    <LaravelReactI18nProvider
        locale={props.initialPage.props.locale || 'pt_BR'}
        fallbackLocale="en"
        files={import.meta.glob('/lang/*.json')}
    >
        <App {...props} />
    </LaravelReactI18nProvider>,
);
```

Vite config addition:

```ts
import i18n from 'laravel-react-i18n/vite';

// Add to plugins array:
i18n();
```

---

## 4. Phase 2: Scan & Analyze

Read [references/detection-rules.md](references/detection-rules.md) before scanning.

### Backend Scanning

1. Use `grep_search` to find string patterns in PHP files within scope
2. Check for strings in translatable contexts (flash messages, exceptions, enum labels, etc.)
3. Cross-reference against existing `__()` usage
4. Assign confidence levels (HIGH, MEDIUM, LOW)

### Frontend Scanning

1. Use `grep_search` and `view_file` to examine TSX/JSX files within scope
2. Look for JSX text content and translatable string props
3. Check for `useLaravelReactI18n` import — if missing, entire file needs setup
4. Assign confidence levels

### Translation File Analysis

1. Parse `lang/*.json` files for existing entries
2. Parse `lang/*/` PHP files for named translations
3. Extract all `__()` and `t()` call arguments from scanned code
4. Compare: keys in code vs. keys in files

---

## 5. Phase 3: Report / Propose Changes

### AUDIT Mode

Read [references/report-template.md](references/report-template.md) and produce a structured report.

Present in conversation (pt-BR summary) with key metrics:

- Compliance rate
- Critical findings count
- Top files needing attention

Save detailed report if user requests it.

### FIX Mode

Group proposed changes by file:

```text
📁 app/Http/Controllers/Admin/ProjectController.php
  L42: 'Project not found' → __('Project not found.')
  L67: 'Created successfully' → __('Created successfully.')

📁 resources/js/pages/admin/dashboard.tsx
  L15: <h1>Dashboard</h1> → <h1>{t('Dashboard')}</h1>
  L23: placeholder="Search" → placeholder={t('Search...')}
  + Add: import { useLaravelReactI18n } from 'laravel-react-i18n';
  + Add: const { t } = useLaravelReactI18n();
```

Also propose new translation entries:

```text
📁 lang/pt_BR.json — New entries:
  "Project not found.": "[NEEDS TRANSLATION]"
  "Created successfully.": "[NEEDS TRANSLATION]"
  "Search...": "[NEEDS TRANSLATION]"
```

### SYNC Mode

Report grouped by category:

- **Missing keys**: used in code but no translation entry
- **Orphaned keys**: in translation files but not used in code
- **Cross-locale gaps**: key exists in one locale file but not another

---

## 6. Phase 4: Approval Gate (FIX/SYNC only)

Present all proposed changes to the user. Ask in pt-BR:

> 📋 **Alterações propostas para revisão.**
> Revise as mudanças acima. Deseja:
>
> 1. ✅ Aprovar todas
> 2. ✏️ Aprovar com ajustes (indique quais)
> 3. ❌ Cancelar

**Never apply changes without explicit approval.**

---

## 7. Phase 5: Apply Changes (FIX/SYNC only)

After approval:

### FIX Mode

1. Edit source files to wrap strings with translation helpers
2. For frontend files: add `useLaravelReactI18n` import and `const { t } = useLaravelReactI18n()` if not present
3. Add new entries to translation JSON files
4. Mark entries needing translation with `[NEEDS TRANSLATION]` placeholder

### SYNC Mode

1. Add missing keys to translation files
2. Optionally remove orphaned keys (only if user approved)
3. Create missing locale files if needed

### Translation Entry Rules

Read [references/key-conventions.md](references/key-conventions.md) when creating translation entries.

- Use JSON-key convention by default (English text as key)
- Add entries to `lang/pt_BR.json` (and `lang/en.json` if it exists)
- Use `[NEEDS TRANSLATION]` for values that need human translation
- If the original hardcoded string was in Portuguese, use it as the pt_BR value and create the English key

---

## 8. Phase 6: Verify

After applying changes:

1. Run `npm run types` to check TypeScript validity (if frontend changes were made)
2. Run `composer lint` to check PHP validity (if backend changes were made)
3. Re-scan modified files to confirm compliance improved
4. Report final compliance rate vs. initial

---

## 9. Guardrails

- **Never modify files without approval** — always AUDIT first, then propose
- **Never install packages without approval** — present setup steps, let user decide
- **Never remove translation entries without confirmation** — orphaned keys might be used dynamically
- **Preserve existing conventions** — if a file already uses named keys, don't switch to JSON-keys
- **Skip test files** — do not audit `tests/` directory unless explicitly requested
- **Skip vendor files** — never modify `vendor/` or `node_modules/`
- **One locale at a time** — when fixing, focus on the primary flow; don't block on translations for all locales

---

## 10. Integration Points

This skill works alongside:

- **After `implement-task` / `task-planner`**: run AUDIT on newly created files
- **Before `project-qa-auditor`**: run SYNC to ensure no translation gaps
- **With `frontend-development`**: ensure new components use `t()` from the start
- **Before PRs**: run AUDIT as a quality check

---

## 11. MCP Tools Used

- `grep_search` — find string patterns in source files
- `view_file` / `view_file_outline` — examine file contents and structure
- `find_by_name` — discover translation files and source files
- `replace_file_content` / `multi_replace_file_content` — apply fixes
- `run_command` — execute lint/type checks for verification
- `application-info` — check installed packages
- `search-docs` — reference Laravel i18n documentation
