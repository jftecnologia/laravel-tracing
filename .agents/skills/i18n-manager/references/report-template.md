# i18n Audit Report Template

Use this template when producing audit reports. Adapt sections based on scope.

---

```markdown
# i18n Audit Report

**Scope:** [Full project | Directory: path | File: path]
**Date:** [YYYY-MM-DD]
**Locales found:** [en, pt_BR]

---

## Summary

| Metric                                           | Count |
| ------------------------------------------------ | ----- |
| Files scanned                                    | X     |
| Hardcoded strings found                          | X     |
| Already translated (using helpers)               | X     |
| Compliance rate                                  | X%    |
| Translation file entries                         | X     |
| Missing translations (keys used but no entry)    | X     |
| Orphaned translations (entries not used in code) | X     |

---

## Backend Findings

### Files with Untranslated Strings

| File                       | Line | String                | Confidence | Recommendation   |
| -------------------------- | ---- | --------------------- | ---------- | ---------------- |
| `app/Http/Controllers/...` | 42   | `'Project not found'` | HIGH       | Wrap with `__()` |

### Backend Summary

- Files checked: X
- Strings found: X
- Already using `__()`: X
- Needing fix: X

---

## Frontend Findings

### i18n Setup Status

| Check                          | Status |
| ------------------------------ | ------ |
| `laravel-react-i18n` installed | ✅/❌  |
| Vite plugin configured         | ✅/❌  |
| Provider in `app.tsx`          | ✅/❌  |

### Files with Untranslated Strings

| File                     | Line | String/Element       | Confidence | Recommendation  |
| ------------------------ | ---- | -------------------- | ---------- | --------------- |
| `resources/js/pages/...` | 15   | `<h1>Dashboard</h1>` | HIGH       | Wrap with `t()` |

### Frontend Summary

- Files checked: X
- Strings found: X
- Already using `t()`: X
- Needing fix: X

---

## Translation File Gaps

### Missing Keys (used in code, no translation entry)

| Key                  | Used In                    | Missing From |
| -------------------- | -------------------------- | ------------ |
| `'Project created.'` | `ProjectController.php:28` | `pt_BR.json` |

### Orphaned Keys (in translation files, not used in code)

| Key                   | File         | Action            |
| --------------------- | ------------ | ----------------- |
| `'Old feature label'` | `pt_BR.json` | Consider removing |

### Cross-Locale Gaps (key exists in one locale but not another)

| Key           | Present In   | Missing From    |
| ------------- | ------------ | --------------- |
| `'Dashboard'` | `pt_BR.json` | — (fallback OK) |

---

## Priority Actions

### Critical (fix immediately)

1. [action]

### Important (fix soon)

1. [action]

### Optional (nice to have)

1. [action]
```
