---
paths:
  - 'lang/**'
---

# Lang

## Key translations by English text and keep JS strings in sync
Key `lang/ms.json` by the exact English string passed to `__()`. Add every `lang/en/js.php` key to `lang/ms/js.php` too. Never use a key that equals a translation group file name (`Pagination`, `Validation`, `Auth`); it resolves to that file and returns an array.
