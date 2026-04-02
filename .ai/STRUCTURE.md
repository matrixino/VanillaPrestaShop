# .ai/ — AI Context Architecture

## Purpose

This folder is the **single source of truth** for all AI-assisted development context in the PrestaShop project. It provides consistent guidance to every AI coding tool used by contributors — whether that's Claude Code, Cursor, GitHub Copilot, Windsurf, Gemini CLI, or a web-based assistant like ChatGPT or Claude.ai.

## Why centralized?

Each AI tool has its own configuration format (`CLAUDE.md`, `.cursorrules`, `.github/copilot-instructions.md`, `AGENTS.md`, `GEMINI.md`, `.windsurfrules`…). Maintaining one context file per tool per domain leads to fragmentation and drift. Instead:

- **This `.ai/` folder** holds all the context, organized by domain and component.
- **Pointer files at the repository root** (`CLAUDE.md`, `AGENTS.md`, `.cursorrules`, etc.) are lightweight bridges that reference this folder. They contain no context themselves — only references.
- **One place to update** — when conventions change, you update the `.ai/` file. Pointer files never need to change.

## Folder structure

```
.ai/
├── CONTEXT.md              # Root context: project-wide rules, architecture, and index of all sub-contexts
├── STRUCTURE.md            # This file — explains the architecture of the .ai/ folder
│
├── skills/                 # Cross-cutting skills not tied to a single domain or component
│   └── {skill-name}/
│       └── SKILL.md
│
├── Domain/                 # Business domain contexts (maps to src/Core/Domain/)
│   ├── {DomainName}/
│   │   └── CONTEXT.md      # Domain-specific conventions, patterns, do/don't rules
│   └── ...
│
└── Component/              # Cross-cutting component contexts (maps to shared infrastructure)
    ├── {ComponentName}/
    │   └── CONTEXT.md      # Component-specific conventions, usage patterns, do/don't rules
    └── ...
```

## File conventions

| File | Purpose | Target size |
|------|---------|-------------|
| `CONTEXT.md` | Conventions, patterns, do/don't rules for a domain or component | < 200 lines |
| `SKILL.md` | Step-by-step task template an AI agent can follow to accomplish a recurring task | < 150 lines |
| `STRUCTURE.md` | This file — architecture documentation | N/A |
| `GOTCHAS.md` | Cross-domain naming traps, identity pitfalls, and legacy mismatches | N/A |
| `MULTISTORE.md` | Cross-cutting multi-store guide: ShopConstraint, scoped config, multi-shop repositories | N/A |

### CONTEXT.md template

Every `CONTEXT.md` follows this structure:

```markdown
# {Domain or Component Name}

## Purpose
[1-2 sentences: what this domain/component does, what it does NOT do]

## Layers
[Table: Layer name | Path — paths only, no class inventories]

## Non-obvious patterns
[Bullet points for things that aren't discoverable by reading the directory structure:
surprising abstractions, delegation chains, legacy gotchas, cross-domain flows]

## Canonical examples
- [File path — 1-line description]

## Related
- [Links to related domains, components, or documentation]
```

**What NOT to include:** class name inventories (commands, queries, exceptions, handlers, value objects). These can always be found by grepping or globbing — listing them wastes context tokens without adding value.

### Writing guidelines

- **Be concise** — use bullet points and tables, not paragraphs. AI parses structured content more reliably.
- **No code dumps** — link to canonical example files, don't inline full class implementations.
- **Describe patterns, not inventories** — write `Handlers follow Domain/{Action}{Entity}Handler.php` instead of listing every handler file.
- **Don't repeat parent context** — domain/component files describe architecture and relationships only. Coding standards, Do/Don't rules, and testing expectations are project-wide and live exclusively in the root `CONTEXT.md`.
- **No tool-specific syntax** — CONTEXT.md files must work for any AI tool or human reader.

## How AI tools discover this context

### Automatic loading (via pointer files at repo root)

| Tool | Pointer file | How it works |
|------|-------------|--------------|
| **Claude Code** | `CLAUDE.md` | Uses `@.ai/CONTEXT.md` reference — loaded at session start. Agent reads domain/component files on demand. |
| **Gemini CLI** | `GEMINI.md` | Instructs Gemini to read `.ai/CONTEXT.md` and domain files when relevant. |
| **Cursor** | `.cursor/rules/*.mdc` | One `.mdc` rule per domain/component with glob patterns to auto-attach. |
| **GitHub Copilot** | `.github/copilot-instructions.md` + `.github/instructions/*.instructions.md` | Repo-wide instructions reference `.ai/CONTEXT.md`. Path-specific files use `applyTo` globs. |
| **Windsurf** | `.windsurf/rules/*.md` | Project-wide rules instruct Cascade to read `.ai/` files when working on matching paths. |
| **AGENTS.md** | `AGENTS.md` | Multi-agent systems reference `.ai/CONTEXT.md`. |

### Web-based assistants (ChatGPT, Claude.ai, Gemini)

Contributors using web-based AI assistants should copy-paste the relevant `CONTEXT.md` file(s) as their initial system prompt:
1. Always start with `.ai/CONTEXT.md` (project-wide rules)
2. Add the relevant domain or component `CONTEXT.md` for the area they're working on

### How an AI agent should navigate this structure

1. **Start with `.ai/CONTEXT.md`** — it contains project-wide rules and an index of all domain/component contexts.
2. **Identify the relevant domain or component** from the index based on the files being worked on.
3. **Read the specific `CONTEXT.md`** for that domain or component.
4. **Check for a matching skill** before performing any recurring task:
   - All skills live in `.ai/skills/{skill-name}/SKILL.md` — check the `## Skills` table in `.ai/CONTEXT.md` or the relevant component/domain `CONTEXT.md`.
   - Read the `SKILL.md` and follow its instructions step by step.

## How to contribute

### Adding context for a new domain

1. Create `.ai/Domain/{DomainName}/CONTEXT.md` using the template above.
2. Add an entry to the index table in `.ai/CONTEXT.md`.
3. Optionally create `.cursor/rules/{domain}.mdc` and `.github/instructions/{domain}.instructions.md` pointer files.

### Adding context for a new component

1. Create `.ai/Component/{ComponentName}/CONTEXT.md` using the template above.
2. Add an entry to the index table in `.ai/CONTEXT.md`.

### Adding a skill

1. Create `.ai/skills/{skill-name}/SKILL.md` — all skills live here regardless of scope.
2. Add one line to the `Current project skills` list in `CLAUDE.md`.
3. Add a `## Skills` entry to the corresponding `CONTEXT.md`: root `.ai/CONTEXT.md` for cross-cutting skills, or the relevant component/domain `CONTEXT.md` for scoped ones.

### Updating existing context

Edit the relevant `CONTEXT.md` directly. Pointer files at the repo root should never need modification — they only contain references.
