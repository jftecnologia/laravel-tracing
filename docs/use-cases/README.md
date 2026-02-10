# Use Cases

This directory contains optional detailed use case documentation for specific scenarios and integration patterns.

## Purpose

Use cases complement the PRD by providing:

- **Detailed user scenarios**: Step-by-step walkthroughs
- **Integration examples**: How the package integrates with other systems
- **Edge case documentation**: Specific scenarios requiring special handling
- **Cross-service flows**: Distributed tracing across multiple services

## When to Create Use Cases

Use cases are **optional** and should be created when:

- A scenario is too detailed for the PRD's user flows section
- Integration patterns need comprehensive documentation
- Edge cases require special handling and documentation
- Real-world examples would clarify requirements

## Document Naming

Use lowercase-with-dashes.md naming:

- `distributed-tracing-across-services.md`
- `custom-user-id-tracking.md`
- `api-gateway-integration.md`
- `job-retry-with-tracing.md`

## Document Structure

```markdown
# [Use Case Title]

**Scenario**: Brief description of the use case

**Actors**: Who/what is involved (User, External Service, Queue Worker, etc.)

**Preconditions**: What must be true before this scenario

---

## Flow

### Step 1: [Action]

Description of what happens

**Input**: What's provided
**Process**: What the system does
**Output**: What results

### Step 2: [Next action]

[Continue...]

---

## Expected Behavior

- Expected result 1
- Expected result 2

## Edge Cases

- Edge case 1 and how it's handled
- Edge case 2 and how it's handled

## Requirements Reference

This use case addresses:
- FR-XX: [Requirement from PRD]
- FR-YY: [Another requirement]

## Architecture Reference

Related architecture components:
- [Component from docs/architecture/]

---

## Example Code

```php
// Code example demonstrating the use case
```

## Testing

How to test this use case:
- [ ] Test step 1
- [ ] Test step 2
```

## Status

This directory is currently **empty**.

Use cases will be added as needed when scenarios require detailed documentation beyond what's in the PRD's user flows section.

## Integration with Task Breakdown

If use cases exist, the `generate-task-breakdown` skill will automatically:

- Read use case documents as additional input
- Reference use cases in task technical notes
- Use use cases to identify edge cases for acceptance criteria

## Creating Use Cases

To create use case documentation:

1. Identify a scenario requiring detailed documentation
2. Create a new markdown file in this directory
3. Follow the document structure above
4. Reference the use case in PRD or architecture docs if relevant
