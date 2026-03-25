---
description: Guide through the full AI-assisted development pipeline from ideation to implementation. Use when starting a new project or major feature from scratch and want to follow the complete structured flow.
---

# Full Development Pipeline

Orchestrate the complete development lifecycle by invoking skills in the correct order.

## Pipeline

```text
Step 1: Brainstorming ──→ Step 2: PRD ──→ Step 3: Architecture ──→ Step 4: UI Design ──→ Step 5: Task Breakdown ──→ Step 6: Implementation
```

## Steps

1. **Determine entry point** — Ask the user:

   > Where are you in the development process?
   >
   > 1️⃣ Starting from zero — begin with brainstorming
   > 2️⃣ Have an idea — skip to PRD
   > 3️⃣ Have PRD — skip to architecture
   > 4️⃣ Have architecture — skip to task breakdown
   > 5️⃣ Have tasks — skip to implementation

2. **Brainstorming** (skill: `brainstorming`)
   - Structured ideation and spec refinement
   - Output: `docs/brainstorming/<NNNN>-<slug>.md`
   - Decision: proceed to PRD or stop?

3. **Product Requirements** (skill: `generate-prd`)
   - Define functional and non-functional requirements
   - Output: `docs/PRD.md` or `docs/prd/<feature>.md`
   - Decision: proceed to architecture or iterate?

4. **Architecture** (skill: `generate-architecture`)
   - System design, database schema, component structure
   - Output: `docs/architecture/*.md`
   - Decision: proceed to UI design or task breakdown?

5. **UI Design** (skill: `generate-ui-design`) — _optional_
   - Design system, page specs, interaction patterns
   - Output: `docs/design/*.md`
   - Skip if: API-only project or no UI changes

6. **Task Breakdown** (skill: `generate-task-breakdown`)
   - Break requirements into implementable tasks with acceptance criteria
   - Output: `docs/tasks/<epic>.md` + `docs/progress/<epic>.md`

7. **Implementation** (skill: `implement-task`)
   - Implement tasks with quality gates
   - For each task: branch → code → lint → test → security → commit
   - Output: working code + progress updates

## Decision Points

After each step, ask the user:

> ✅ Step complete. Options:
> 1️⃣ Continue to next step
> 2️⃣ Iterate on this step
> 3️⃣ Skip next step
> 4️⃣ Stop here for now

> ⚠️ **Planning gate**: Each planning step must include Security and i18n considerations (see `AGENT_FLOW.md` §3.1)
> ⚠️ **Post-code gate**: Implementation steps must execute Mandatory Gates (see `AGENT_FLOW.md` §3.2)
