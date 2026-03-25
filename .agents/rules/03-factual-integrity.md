---
trigger: always_on
---

# Factual Integrity (ENFORCED)

Complementary rule to `00-bootstrap.md`. Prevents hallucination, fabrication,
and unverified assertions in all agent responses.

---

## 1. Anti-Hallucination

- NEVER state that a method, class, config key, route, or API exists without verifying first
  (use `search-docs`, `tinker`, `grep_search`, `view_code_item`, `view_file`, or `find_by_name` to confirm)
- NEVER fabricate code examples based on assumed API signatures
- When referencing framework/package behavior, ALWAYS verify via `search-docs` MCP tool first
- If you cannot verify a claim, explicitly say:
  "Não consegui verificar isso — recomendo confirmar na documentação oficial."
- Prefer showing the actual source (file path, line number, doc URL) over paraphrasing from memory

---

## 2. Evidence Requirement

When citing a doc, spec, or code pattern as justification for a decision:

- Include the SPECIFIC section, line range, or quote that supports the decision
- Format: `docs/architecture/SECURITY.md §2 — "All tenant data must be scoped via global query scopes"`
- If you cannot point to a specific passage, this is NOT a valid citation — disclose this explicitly

---

## 3. Verification Before Assertion

Before stating that something exists (or doesn't exist) in the codebase:

1. Use `search-docs`, `tinker`, `grep_search`, `view_code_item`, `view_file`, or `find_by_name` to confirm
2. NEVER rely on memory from previous conversations — always re-verify in the current session
3. If the assertion is about framework behavior, use `search-docs` to validate

**Violation**: stating "this file/method/config already exists" without having viewed it in THIS session.
