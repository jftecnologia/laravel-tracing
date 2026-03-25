# Severity Classification

Rules for classifying and prioritizing findings in code review reports.

---

## Severity Levels

| Severity | Icon | Description | Action Required |
| --- | --- | --- | --- |
| **Critical** | 🔴 | Active bug, major spec violation, or conflicting business logic causing incorrect results | Must fix — blocks correctness |
| **High** | 🟠 | Significant tech debt, maintenance risk, or security flaw that could lead to problems | Should fix soon |
| **Medium** | 🟡 | Code smell, improvement opportunity, or minor inconsistency | Recommend fixing |
| **Low** | 🔵 | Minor suggestion, style preference, or defense-in-depth improvement | Consider fixing |
| **Info** | ⚪ | Observation, note, or inventory item (e.g., TODO count) | Awareness only |

---

## Classification Criteria by Category

### Dead Code

| Finding | Default Severity |
| --- | --- |
| Unused class in active code path | 🟡 Medium |
| Unused method in public API | 🟡 Medium |
| Unreachable code after return/throw | 🔵 Low |
| Orphaned config value | 🔵 Low |
| TODO/FIXME older than 6 months | 🟡 Medium |
| TODO/FIXME recent | ⚪ Info |
| Unused route | 🟡 Medium |
| Unused database column (in model) | 🟡 Medium |

### Code Quality, SOLID & Code Smells

| Finding | Default Severity |
| --- | --- |
| God class (>500 LOC, >15 methods) | 🟠 High |
| God method (>50 LOC, complexity >10) | 🟠 High |
| Single Responsibility violation (clear) | 🟠 High |
| Missing dependency injection | 🟡 Medium |
| Interface segregation violation | 🟡 Medium |
| Code duplication (>20 lines identical) | 🟡 Medium |
| Code duplication (<20 lines) | 🔵 Low |
| Missing type hints | 🔵 Low |
| Naming inconsistency | 🔵 Low |
| Overengineering (1 implementation for abstract) | 🟡 Medium |
| Feature Envy (clear, across layers) | 🟡 Medium |
| Long Parameter List (>4 params) | 🟡 Medium |
| Shotgun Surgery (systemic) | 🟠 High |
| Data Clumps (>3 occurrences) | 🟡 Medium |
| Primitive Obsession (domain values) | 🔵 Low |
| Inappropriate Intimacy | 🟡 Medium |
| Message Chains (>3 calls) | 🔵 Low |
| Dead Stores | 🔵 Low |
| Speculative Generality | 🟡 Medium |

### Configuration & Hardcoded Values

| Finding | Default Severity |
| --- | --- |
| Hardcoded credentials/secrets | 🔴 Critical |
| `env()` call outside `config/` | 🟠 High |
| Hardcoded API URLs | 🟡 Medium |
| Magic numbers in business logic | 🟡 Medium |
| Magic strings for status/role | 🟡 Medium |
| Tenant-configurable value hardcoded | 🟡 Medium |
| Hardcoded pagination limit | 🔵 Low |
| Missing config file for behavior | 🔵 Low |

### Spec Compliance & Business Rules

| Finding | Default Severity |
| --- | --- |
| Conflicting calculations across files | 🔴 Critical |
| Logic contradicts PRD requirement | 🔴 Critical |
| Inconsistent business rules | 🟠 High |
| Permission logic differs from RBAC guide | 🟠 High |
| Status transition not in spec | 🟠 High |
| Missing edge case from spec | 🟡 Medium |
| Minor spec deviation (cosmetic) | 🔵 Low |

### Test Quality

| Finding | Default Severity |
| --- | --- |
| Controller/Action with zero tests | 🟠 High |
| Policy with zero tests | 🟠 High |
| Tests only cover happy path (no unhappy/security) | 🟡 Medium |
| Orphaned test (tests deleted class) | 🟡 Medium |
| Missing cross-tenant isolation test | 🟠 High |
| Test asserts trivial value (`assertTrue(true)`) | 🟡 Medium |
| Tests use PHPUnit syntax instead of Pest | 🔵 Low |
| Inconsistent test structure | 🔵 Low |
| Missing test for security path | 🟡 Medium |
| Hardcoded IDs/data in tests | 🔵 Low |
| Critical page without any rendering test (login, dashboard) | 🟠 High |
| CRUD page without feature test for rendering + data | 🟡 Medium |
| Multi-step UI flow without browser test | 🟡 Medium |
| Page test doesn't verify expected props/data | 🟡 Medium |
| Browser tests missing `assertNoJavaScriptErrors()` | 🔵 Low |
| No smoke tests for main pages | 🟡 Medium |
| Business calculation without unit test | 🟠 High |
| Status transition without test coverage | 🟠 High |
| PRD-defined business rule without any test | 🟡 Medium |
| Business validation without rejection test | 🟡 Medium |

---

## Priority Escalation Rules

Increase severity by one level when:

- Finding affects **multiple files** (systemic issue)
- Finding is in a **security-sensitive** area (auth, payment, tenant isolation)
- Finding involves **user-facing** behavior (visible errors to end users)
- Finding has been present for **>6 months** (stale debt)

Decrease severity by one level when:

- Finding is in **non-production** code (seeders, dev tooling)
- Finding is in a **deprecated** feature scheduled for removal
- Finding has a **documented workaround** or mitigating control

---

## Report Result Classification

| Result | Criteria |
| --- | --- |
| 🟢 **HEALTHY** | 0 critical, 0 high, ≤5 medium findings |
| 🟡 **NEEDS ATTENTION** | 0 critical, ≤3 high, or >5 medium findings |
| 🔴 **NEEDS REMEDIATION** | Any critical finding, or >3 high findings |
