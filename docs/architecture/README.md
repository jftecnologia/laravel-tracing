# Architecture Documentation

This directory contains architecture documentation for the Laravel Tracing package.

## Purpose

Architecture documents define the technical organization and structure of the package:

- **Component design**: Classes, responsibilities, and relationships
- **Data flows**: How data moves through the system
- **Extension points**: Where and how to extend the package
- **Technical decisions**: Rationale behind architectural choices

## Document Naming

Architecture documents use **UPPERCASE.md** naming to distinguish them from other documentation:

- `STRUCTURE.md` - Package directory structure and organization
- `COMPONENTS.md` - Core components and their responsibilities
- `DATA_FLOW.md` - Request lifecycle and data flow patterns
- `CONFIGURATION.md` - Configuration architecture
- `EXTENSIONS.md` - Extension and customization architecture

## Creating Architecture Documentation

To generate architecture documentation, ask Claude Code to:

```
"Gere a documentação de arquitetura do pacote"
```

Claude will automatically use the `generate-architecture` skill to create comprehensive architecture documentation based on:

- `docs/PRD.md` (requirements)
- `docs/engineering/CODE_STANDARDS.md` (development philosophy)
- `docs/engineering/STACK.md` (technical constraints)
- Existing architecture documents (for updates)

## When to Update

Update architecture documentation when:

- Adding new major features
- Changing component structure
- Modifying extension points
- Making architectural decisions that affect future development

Architecture documents should stay synchronized with the actual codebase.
