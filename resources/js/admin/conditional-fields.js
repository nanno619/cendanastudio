/*
 * Shows a block only while another field in the same form has one of the given values. Markup:
 *   <select name="marital_status">…</select>
 *   <div data-show-when="marital_status" data-show-values="married,divorced,widowed">…fields…</div>
 * Fields inside a hidden block are disabled, so they aren't posted or checked by the browser; the server
 * decides what's saved. Without JS the server's initial `hidden` stays and the block's values post as is.
 */
function controlFor(block) {
  const scope = block.closest('form') || document;
  return scope.querySelector('[name="' + CSS.escape(block.dataset.showWhen) + '"]');
}

function update(block) {
  const control = controlFor(block);
  if (!control) return;

  const values = (block.dataset.showValues || '').split(',').map((value) => value.trim()).filter(Boolean);
  const visible = values.includes(control.value);
  block.hidden = !visible;
  block.querySelectorAll('input, select, textarea').forEach((field) => { field.disabled = !visible; });
}

document.addEventListener('change', (event) => {
  const name = event.target && event.target.name;
  if (!name) return;
  const scope = event.target.form || document;
  scope.querySelectorAll('[data-show-when="' + CSS.escape(name) + '"]').forEach(update);
});

function initConditionalFields(scope = document) {
  scope.querySelectorAll('[data-show-when]').forEach(update);
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initConditionalFields());
else initConditionalFields();
