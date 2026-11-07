---
paths:
  - 'docs/**'
---

# Docs

## Keep the VitePress docs buildable and in step with the code
Wrap inline code containing `{{ }}` in `<span v-pre>`; Vue evaluates it otherwise. Add or rename pages in the `docs/.vitepress/config.mts` sidebar too. Write examples with `__()` and English slugs, then run `npm run docs:build`.
