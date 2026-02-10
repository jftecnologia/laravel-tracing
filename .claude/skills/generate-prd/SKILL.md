---
name: generate-prd
description: Generate a Product Requirements Document (PRD)
disable-model-invocation: true
---

Generate a **Product Requirements Document (PRD)** for the project or module described in: $ARGUMENTS.

Follow the steps below **strictly**.

---

## 1. Load Skill Rules

1. Read and follow the PRD skill rules defined in this file.
2. The PRD is the **single source of truth** for product scope and intent.
3. Focus on **what** and **why**, never on **how**.

---

## 2. Clarify Missing Information

1. Identify missing or ambiguous information required to generate a complete PRD.
2. Ask **only the minimum necessary questions** to proceed.
3. Ask questions in **Portuguese (pt-BR)**.
4. Do **not** guess or assume requirements.

---

## 3. PRD Language & Style

1. Write the PRD **in English**.
2. Be concise, explicit, and unambiguous.
3. Avoid buzzwords, marketing language, and vague statements.
4. Prefer bullet points and clearly structured sections.

---

## 4. Mandatory PRD Structure

Generate the PRD using **exactly** the structure below.

---

# Product Requirements Document (PRD)

## 1. Overview

### 1.1 Product Name

### 1.2 Problem Statement

### 1.3 Objectives

---

## 2. Stakeholders

---

## 3. Target Users

---

## 4. Scope

### 4.1 In Scope

### 4.2 Out of Scope

---

## 5. Functional Requirements

- FR-01:
- FR-02:

Requirements must describe **observable behavior** and be **testable**.

---

## 6. Non-Functional Requirements

Include only relevant items:

- Performance
- Security
- Availability
- Compliance
- Scalability
- Usability

---

## 7. User Flows (High-Level)

Describe user interactions step-by-step, without diagrams.

---

## 8. Assumptions & Constraints

### 8.1 Assumptions

### 8.2 Constraints

---

## 9. Risks & Mitigations

---

## 10. Success Metrics

---

## 11. Open Questions

---

## 5. Output Rules

1. Do **not** include:
    - Architecture diagrams
    - Database models
    - API definitions
    - UI mockups
2. Do **not** design the solution.
3. Keep scope explicit and bounded.

---

## 6. File Output

1. Write the final PRD to the documentation folder:
    - Default: `docs/PRD.md`
2. If a product or module name is provided:
    - Use: `docs/prd/<semantic-slug>.md`

---

## 7. Completion Criteria

The PRD is complete only if:

1. All sections are filled.
2. Scope is clearly defined (in / out).
3. Requirements are testable.
4. Open questions are explicitly listed.
5. The document can be approved or rejected by stakeholders.
