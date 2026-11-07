---
paths:
  - config/cendana.php
---

# Config

## Keep config strings English and untranslated
Store labels, accent/radius names and demo values as plain English; never call `__()` in config, because config loads before the locale is set. Views wrap them in `__()`. Status/filter keys stay English slugs (`active`, `in-review`) matched by JS `data-status` filters.

## Keep demo pages behind the showcase flag
Register new showcase or example routes inside the `if (config('cendana.showcase'))` block, and give their navigation group `'showcase' => true`, so `ADMIN_SHOWCASE=false` hides them.
