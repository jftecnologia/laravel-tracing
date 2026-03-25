---
name: generate-persona
description: Generate a structured persona profile (user, system role, or external agent) based on user instructions and project files (like demo seeders or docs). Use when the user asks to "create a persona", "define a user profile", "generate a persona for X", or "create a persona file".
---

# Generate Persona

Create detailed, realistic persona profiles that can serve as context for UI/UX testing, feedback generation, and auditing. This skill extracts and structures demographic, behavioral, and technical information to simulate how a specific persona would interact with the system.

**Output:** A Markdown document in `docs/personas/<slug>.md`

## Workflow

1.  **Understand the Request:**
    - Identify the persona's name, role, or type requested by the user.
    - Determine if the persona is a **known system user** (exists in seeders/DB) or a **generic persona** (a role/archetype description).
    - Example: "Crie a persona do Marcelo Duarte" (known user) or "Crie a persona do Pentester" (generic).

2.  **Context Gathering (Crucial Step):**
    - If the persona is a **known system user**, inspect the database seeders (e.g., `database/seeders/`) using `view_file` or `grep_search`. Extract name, email, roles, tenant associations, company associations, and default permissions. **Always include the user's email** in the persona document.
    - If the persona is a **generic role/archetype**, check architecture docs or code for permissions and expected behavior. Do NOT invent an email.
    - If the user provides context directly in the prompt, use it.
    - **Important:** Distinguish precisely between multi-tenant (user belongs to multiple tenants) and multi-client (user belongs to multiple companies within a single tenant). These are different scenarios.

3.  **Synthesis & Structuring:**
    - Structure the collected data using the template located at `[persona-template.md](references/persona-template.md)`.
    - **Crucial:** Pay deep attention to "Comportamento e Hábitos de Uso" and "Forma de Acesso e Interação". Ensure the profile describes realistic constraints (e.g., impatient user, mobile-first, only views dashboards). Let's avoid generic descriptions; make them highly opinionated based on the role.

4.  **Creation:**
    - Generate the persona document in English (for AI consumption) or Portuguese (if specified by the user). By default, write the content in Portuguese for better alignment with Portuguese-speaking project teams, while keeping structure/titles aligned with the template.
    - **File naming conventions:**
      - **Known system user:** `docs/personas/<descriptive-slug>-<user-name>.md`. Example: `multi-client-single-tenant-marcelo-duarte.md`.
      - **Generic persona:** `docs/personas/<role-slug>.md`. Example: `tenant-admin.md`, `pentester.md`.
    - Create the `docs/personas/` directory if it doesn't exist.

5.  **Completion:**
    - Inform the user the persona has been successfully generated and is ready to be used by other testing/feedback skills.

## References

Always follow the exact structure defined in the reference file:

- [persona-template.md](references/persona-template.md) - The standard template for personas.

## Examples

- **User:** "Crie a persona do Tenant Admin."
  - **Agent Action:** Generic persona. Inspects seeders/docs for what a tenant admin does. Creates `docs/personas/tenant-admin.md` (generic slug, no email).
- **User:** "Gere uma persona para o João Silva."
  - **Agent Action:** Known user. Greps seeders for "João Silva". Finds email `joao@example.test`, role Admin in Organization "Acme Corp", associated with Team Alpha (admin) and Team Beta (readonly). Creates `docs/personas/multi-team-joao-silva.md` (descriptive slug with name, email included in doc).
