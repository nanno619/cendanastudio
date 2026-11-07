---
name: commit
description: Use when creating a git commit and the message should follow Conventional Commits format (type(scope): description) rather than free-form prose — including when repo history already uses type(scope) prefixes.
---

# Commit

## Overview
Formats commit messages per the Conventional Commits spec: `type(scope): description`, with an optional bullet body. Use in place of open-ended commit prose when the message needs to be machine-parseable (changelog tooling, semantic-release, commitlint) or when the repo's own history already follows this convention.

## When to Use
- User asks for a "conventional commit" or the repo has commitlint/semantic-release configured.
- `git log --oneline -20` shows existing commits already using `type(scope):` prefixes — match the convention even if not asked.
- Not needed where the project's own commit convention is free-form prose (check `git log` first either way).

## Workflow
1. `git diff --staged` — read the actual change, not just filenames.
2. Pick exactly one `type` from the table below for the change's primary purpose. If a diff mixes concerns (e.g. a fix plus incidental test updates), pick the type that matters most to consumers of the log and leave the rest out of the subject.
3. Pick a `scope`: the module, package, or directory most affected. Omit scope for repo-wide changes.
4. Write `description`: imperative mood, lowercase after the colon, no trailing period.
5. Add a body only if the "why" isn't obvious from the subject line — terse imperative bullets, not prose paragraphs.
6. Commit using this project's existing git commit protocol (heredoc message, no hook-skipping, no amending unless asked) — but omit the `Co-Authored-By: Claude ...` trailer even if your default git workflow normally appends one. Conventional Commits footers are reserved for `BREAKING CHANGE:` and issue references; a tool-attribution trailer doesn't belong there.

## Types
| Type | Use for |
|---|---|
| feat | new capability visible to users/consumers |
| fix | bug fix |
| docs | documentation only |
| style | formatting, no logic change |
| refactor | code change that is neither a fix nor a feature |
| test | adding or adjusting tests only |
| chore | tooling, deps, build config, maintenance |

## Example
Diff: adds a guard in `apply_discount` that raises on negative totals.

```
fix(cart): reject negative totals in apply_discount

Prevents downstream discount math from silently operating on
invalid negative totals.
```

Not: `Reject negative totals in apply_discount` — no type or scope, the failure mode this skill exists to prevent.

## Common Mistakes
- Guessing the type from a restated summary instead of reading `git diff --staged`.
- Using `feat` for internal refactors with no user-visible behavior change — use `refactor`.
- Multi-paragraph prose body instead of terse bullets.
- Capitalizing the first letter after the colon, or adding a trailing period.
- Appending a `Co-Authored-By: Claude ...` trailer — this format doesn't use it.
