# Browser QA Report: [Scope/Flow Name]

**Date:** [YYYY-MM-DD]
**Persona Evaluated:** [e.g., Global Admin, Pentester, Client]
**Target URL Tested:** [e.g., `/dashboard`, `/tenant/1/users`]
**Result:** [✅ PASS | ⚠️ WARNINGS | ❌ FAIL]

---

## 1. Executive Summary
[A brief 1-2 paragraph summary of the testing session. Explain what was tested, the overall experience, and the most critical findings. Did it meet the acceptable usability and visual standards? Was the multi-tenant isolation intact?]

---

## 2. 🎯 Detections & Findings
[Detailed breakdown of what the AI browser agent observed during the session.]

### 2.1 Technical & Console Issues
- [Identify any console errors, 404s, or failed network requests]
- [Identify performance hiccups or long loading states]

### 2.2 Functional & Multi-Tenant Boundaries
- [Did the user successfully complete the intended flow?]
- [Were there any violations or concerns regarding cross-tenant data access or manipulation?]

---

## 3. ⚖️ Evaluation

### ✅ Positives (Pros)
- [List 2-4 positive aspects of the UX/UI]
- [e.g., "The alignment in the navigation sidebar provides an excellent visual hierarchy."]

### ❌ Negatives (Cons)
- [List the primary negative aspects or friction points]
- [e.g., "Primary and secondary buttons share the exact same styling, confusing the user on what action completes the form."]

---

## 4. 💡 Actionable Proposals

[Based on the detections and negatives, provide concrete proposals for improvement. Justify each one.]

1. **[Proposal Title]**
   - **Observation:** [What is currently happening]
   - **Proposal:** [What should be changed, e.g., "Update the cancel button to use `.btn-secondary` or `.bg-transparent`"]
   - **Justification:** [Why this improves the UX based on the heuristics]

2. **[Proposal Title]**
   - **Observation:** [What is currently happening]
   - **Proposal:** [What should be changed]
   - **Justification:** [Why this improves the UX]

---

## 5. Required Next Steps
- [ ] [Usually: Create a GitHub issue or immediately proceed to `bug-fixer`/`frontend-development` to implement the Actionable Proposals]
