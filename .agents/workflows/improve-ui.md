---
description: "Improve UI/UX quality — audit visual consistency, design system compliance, and user experience, then route to the right skills to fix issues. Use when an interface looks basic, inconsistent, or doesn't follow project design standards (e.g., 'improve this UI', 'fix the design', 'make this look better', 'this page is ugly', 'align with design system', 'melhorar a interface', 'está feio')."
---

# Improve UI/UX

Audit and improve the visual quality, consistency, and user experience of interfaces.

## Steps

1. **Identify the target**:
   - Which page(s) or component(s) need improvement?
   - If user says "tudo" or is vague, start with the main pages

2. **Visual Audit** — Use browser subagent to inspect:
   - Navigate to the target page(s)
   - Take screenshots for reference
   - Check against project design system (`docs/design/DESIGN_SYSTEM.md` if exists)
   - Assess:
     - [ ] Color consistency (design tokens, not ad-hoc)
     - [ ] Typography (consistent font sizes, weights, families)
     - [ ] Spacing (consistent padding, margins, gaps)
     - [ ] Component usage (using existing components vs custom HTML)
     - [ ] Dark/light mode support
     - [ ] Tenant branding colors respected
     - [ ] Responsive behavior
     - [ ] Visual hierarchy (headings, sections, CTAs)
     - [ ] Micro-interactions (hover states, transitions, loading)
     - [ ] Empty states (what happens when there's no data)
     - [ ] Error states (validation, server errors)

3. **Code Audit**:
   - Read the component/page source code
   - Check for:
     - Hardcoded colors/sizes instead of design tokens
     - Missing Radix UI / existing component usage
     - Inconsistent CSS patterns (inline vs Tailwind vs custom)
     - Missing accessibility attributes (`aria-*`, roles)
     - Components that should be reused but were copy-pasted

4. **Gap Analysis** — Categorize issues:

   ```markdown
   ## 🎨 UI/UX Audit Results

   ### 🔴 Critical (breaks design system)

   - [issue]: [file:line]

   ### 🟠 High (visual inconsistency)

   - [issue]: [file:line]

   ### 🟡 Medium (could be better)

   - [issue]: [file:line]

   ### 🔵 Enhancement (nice to have)

   - [issue]: [file:line]
   ```

5. **Recommend path**:

   ```markdown
   ## 🎯 Sua Decisão

   Baseado na auditoria, recomendo:

   1️⃣ **Quick fix** — Corrigir issues críticos e high agora (~X arquivos)
   2️⃣ **Design spec first** — Criar spec com `generate-ui-design`, depois implementar
   3️⃣ **Full redesign** — Brainstorming → Design → Implement (pipeline completo)
   4️⃣ **Browser QA** — Rodar `browser-qa-tester` para auditoria completa

   > Responda o número ou comente livremente.
   ```

6. **Execute** (based on user choice):
   - **Quick fix**: Route to `implement-task` + `frontend-development` skill
     - Apply design tokens, replace ad-hoc styles
     - Use existing components, fix dark/light mode
     - Ensure tenant branding is respected
   - **Design spec first**: Route to `generate-ui-design` skill
   - **Full redesign**: Route to `/full-pipeline` starting at UI design step
   - **Browser QA**: Route to `browser-qa-tester` skill

7. **Validate**:
   - Take screenshots after changes
   - Compare before/after visually
   - Check light mode AND dark mode
   - Check tenant branding colors
   - Run lint to ensure clean code

## Design System Checklist

When fixing UI issues, always ensure:

- Use `class-variance-authority` for component variants
- Use `tailwind-merge` + `clsx` for conditional classes
- Use OKLCH color space for dark mode derivation
- Reuse components from `resources/js/components/` before creating new ones
- Follow spacing scale from design system
- Support both light and dark modes
- Respect tenant-customizable colors (primary, secondary)

> ⚠️ **Post-Code Gates**: Before committing UI changes, execute Mandatory Gates — especially i18n check (see `AGENT_FLOW.md` §3.2)
