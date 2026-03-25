# Brainstorming Output Template

Use this template when generating brainstorming documents. Adapt sections based on the brainstorming type — omit irrelevant sections, expand where complexity demands.

---

```markdown
# Brainstorming: [Title]

**Type:** [new-project | feature | architecture | skill | refactor | bug-analysis | layout | guidance | general]
**Date:** [YYYY-MM-DD]
**Status:** [Draft | Approved | Superseded]

---

## 1. Context

[What prompted this brainstorming. Current state of things. Background information needed to understand the discussion.]

## 2. Problem Statement

[Clear, concise description of what we're trying to solve or achieve.]

## 3. Goals

- [Goal 1]
- [Goal 2]
- [Goal 3]

## 4. Constraints

- [Technical constraints]
- [Business constraints]
- [Time/resource constraints]

---

## 5. Options Explored

### Option A: [Name]

**Description:** [How this option works]

**Pros:**

- [advantage]

**Cons:**

- [disadvantage]

**Effort:** [Low / Medium / High]
**Risk:** [Low / Medium / High]
**Impact:** [Low / Medium / High]

### Option B: [Name]

[Same structure as Option A]

### Option C: [Name] (if applicable)

[Same structure as Option A]

---

## 6. Comparison Matrix

| Criteria                    | Option A | Option B | Option C |
| --------------------------- | -------- | -------- | -------- |
| Effort                      |          |          |          |
| Risk                        |          |          |          |
| Impact                      |          |          |          |
| Maintainability             |          |          |          |
| User Experience             |          |          |          |
| Alignment with architecture |          |          |          |

---

## 7. Recommendation

**Selected:** [Option X]

**Rationale:** [Why this option is the best choice given the constraints and goals.]

---

## 8. MVP Scope (if applicable)

### Must Have (MVP)

- [item]

### Should Have (v1.1)

- [item]

### Could Have (future)

- [item]

### Won't Have (explicitly excluded)

- [item]

---

## 9. Open Questions

- [ ] [Question that still needs answering]
- [ ] [Decision that still needs to be made]

---

## 10. Next Steps

- [Recommended next action with skill reference]

---

## 11. References

<!-- All file references MUST use project-relative paths, never absolute file:/// URIs -->

- [DEMO_DATA.md](docs/DEMO_DATA.md) — example relative reference
- [UserRole.php](app/Enums/UserRole.php) — example relative reference
```

---

## Type-Specific Sections

### For `new-project` — Add after Section 3:

```markdown
## Name Candidates

| Name   | Rationale | Domain Available | Verdict                 |
| ------ | --------- | ---------------- | ----------------------- |
| [name] | [why]     | [yes/no/unknown] | [chosen/rejected/maybe] |

## Target Audience

- **Primary:** [who]
- **Secondary:** [who]

## Monetization (if applicable)

[Revenue model considerations]
```

### For `layout` — Add after Section 4:

```markdown
## User Flows

### Flow 1: [Name]

1. [Step]
2. [Step]
3. [Step]

## Component Structure

[Logical hierarchy of UI components and their relationships]

## Responsive Strategy

| Breakpoint          | Layout Changes |
| ------------------- | -------------- |
| Mobile (<768px)     | [changes]      |
| Tablet (768-1024px) | [changes]      |
| Desktop (>1024px)   | [changes]      |

## Visual Hierarchy

[Information priority and how emphasis is distributed across the interface]
```

### For `architecture` — Add after Section 7:

```markdown
## Migration Plan

1. [Step with estimated effort]
2. [Step]
3. [Step]

## Rollback Strategy

[How to revert if things go wrong]

## Performance Impact

[Expected improvements or regressions with metrics if possible]
```

### For `skill` — Add after Section 7:

```markdown
## Skill Integration

**Trigger phrases:** [when should this skill activate]
**Input:** [what the skill receives]
**Output:** [what the skill produces]
**Dependencies:** [other skills it interacts with]
**Workflow position:** [where it fits in the development pipeline]
```

### For `guidance` — Replace Sections 5-7 with:

````markdown
## 5. Skill & Workflow Recommendations

### Primary Recommendation

| Skill/Workflow | Match       | What It Does  | Output             |
| -------------- | ----------- | ------------- | ------------------ |
| [name]         | ⭐⭐⭐ High | [description] | [what it produces] |

**Rationale:** [Why this is the best fit for the user's needs]

### Alternative Options

| Skill/Workflow | Match       | What It Does  | When to Prefer                  |
| -------------- | ----------- | ------------- | ------------------------------- |
| [name]         | ⭐⭐ Medium | [description] | [scenario where this is better] |
| [name]         | ⭐ Low      | [description] | [scenario where this is better] |

## 6. Recommended Pipeline

```text
[skill A] → [skill B] → [skill C]
```

**Step-by-step:**

1. **[Skill A]** — [what to do and why]
2. **[Skill B]** — [what to do and why]
3. **[Skill C]** — [what to do and why]

## 7. Gap Analysis

### Covered by Existing Skills

- [capability 1]
- [capability 2]

### NOT Covered (Manual Effort Required)

- [gap 1 — suggestion for how to handle]
- [gap 2 — suggestion for how to handle]
````

### When External Research was performed (Phase 1.7) — Add after Section 4:

```markdown
## External Research & Market Context

### [Product/Tool Name]

- **URL:** [source URL]
- **Key Features:** [list of relevant features]
- **Relevant Insights:** [what we can learn or adapt]
- **Differentiators:** [how it differs from our approach]

### [Product/Tool Name 2] (if applicable)

[Same structure]

### Synthesis

[Cross-cutting insights from all researched tools. Patterns, trends, and opportunities identified.]
```

Also update Section 11 (References) to include external sources:

```markdown
## 11. References

### Internal

<!-- All file references MUST use project-relative paths, never absolute file:/// URIs -->

- [DEMO_DATA.md](docs/DEMO_DATA.md) — example relative reference

### External (from Phase 1.7 research)

- [Product Name](URL) — Accessed on [YYYY-MM-DD]. [Brief description of what was referenced]
```
