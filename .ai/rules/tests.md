---
paths:
  - 'tests/**'
---

# Tests

## Assert configured values, cover every page
Assert brand text through `config('cendana.*')`, never literal names, because tests read `.env` branding. Add each new admin page to the page list in `ExampleTest::test_cendana_pages_render_successfully`.
