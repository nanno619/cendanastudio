---
paths:
  - routes/web.php
---

# Routes

## Route::view for static pages, invokable controllers for actions
Register static pages with `Route::view()`. Give a real action its own single-action invokable controller (`__invoke`), not a multi-method or resource controller.

## Read the laravel-best-practices skill before writing Laravel PHP
Before creating, changing or reviewing Laravel PHP here (controllers, models, migrations, routes, form requests, policies, jobs, queries), activate the `laravel-best-practices` skill (`.claude/skills/laravel-best-practices`) and follow it. Where it conflicts with this project's own rules, the project rules win.

## Keep demo pages behind the showcase flag
Register new showcase or example routes inside the `if (config('cendana.showcase'))` block, and give their navigation group `'showcase' => true`, so `ADMIN_SHOWCASE=false` hides them.
