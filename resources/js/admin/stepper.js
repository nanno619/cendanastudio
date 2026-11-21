/*
 * Step-by-step forms. Markup:
 *   <form data-stepper>
 *     <ol data-stepper-nav hidden> <li><button type="button" data-stepper-go="0">…<span class="step">1</span>…</button></li> … </ol>
 *     <div data-step="Account">…fields…</div> … <div data-step="Review" data-stepper-review><div data-stepper-summary></div></div>
 *     <button type="button" data-stepper-prev hidden> <button type="button" data-stepper-next hidden> <button type="submit" data-stepper-submit>
 *   </form>
 * Without JS every step shows and the form posts as one page. With JS one step shows at a time; Next checks
 * the step's fields with the browser's own validation first; the step nav jumps back, or forward once the
 * steps before are valid. After a failed server-side save, the form opens on the first step with an error.
 * A review step lists each step's answers before submitting. Strings come from lang/{en,ms}/js.php.
 */
function initStepper(form) {
  if (form._stepper) return;

  const steps = Array.from(form.querySelectorAll('[data-step]'));
  if (steps.length < 2) return;

  const nav = form.querySelector('[data-stepper-nav]');
  const goButtons = Array.from(form.querySelectorAll('[data-stepper-go]'));
  const prev = form.querySelector('[data-stepper-prev]');
  const next = form.querySelector('[data-stepper-next]');
  const submit = form.querySelector('[data-stepper-submit]');
  const status = form.querySelector('[data-stepper-status]');
  let current = 0;

  const fields = (step) => Array.from(step.querySelectorAll('input, select, textarea'))
    .filter((field) => field.name && !field.disabled && field.type !== 'hidden');

  const firstInvalid = (step) => fields(step).find((field) => !field.checkValidity());

  const hasServerError = (step) => step.querySelector('[aria-invalid="true"], .input-invalid, .field-error') !== null;

  const labelFor = (field) => {
    const label = field.id ? form.querySelector('label[for="' + CSS.escape(field.id) + '"]') : null;
    const text = label ? (label.querySelector('.font-medium') || label).textContent : field.name;
    return text.replace('*', '').trim();
  };

  const valueOf = (field) => {
    if (field.type === 'checkbox') return field.checked ? __t('yes') : __t('no');
    if (field.tagName === 'SELECT') {
      const option = field.options[field.selectedIndex];
      return option && option.value !== '' ? option.textContent.trim() : '';
    }
    // Fields styled `uppercase` (official name, employee number) are saved in capitals, so show them that way.
    const value = field.value.trim();
    return getComputedStyle(field).textTransform === 'uppercase' ? value.toLocaleUpperCase() : value;
  };

  function renderSummary() {
    const summary = form.querySelector('[data-stepper-summary]');
    if (!summary) return;
    summary.replaceChildren();

    steps.forEach((step, index) => {
      if (step.hasAttribute('data-stepper-review')) return;

      const section = document.createElement('section');
      section.className = 'space-y-3';
      const heading = document.createElement('div');
      heading.className = 'flex items-center justify-between gap-3';
      const title = document.createElement('h3');
      title.className = 'text-sm font-semibold';
      title.textContent = step.dataset.step;
      const edit = document.createElement('button');
      edit.type = 'button';
      edit.className = 'btn btn-ghost btn-sm';
      edit.textContent = __t('edit');
      edit.addEventListener('click', () => show(index));
      heading.append(title, edit);

      const list = document.createElement('dl');
      list.className = 'grid gap-x-8 gap-y-3 sm:grid-cols-2 lg:grid-cols-3';
      const seen = new Set();
      fields(step).forEach((field) => {
        if (seen.has(field.name)) return;
        seen.add(field.name);
        const item = document.createElement('div');
        item.className = 'min-w-0';
        const term = document.createElement('dt');
        term.className = 'text-xs font-medium text-muted-foreground';
        term.textContent = labelFor(field);
        const detail = document.createElement('dd');
        detail.className = 'mt-0.5 break-words text-sm font-medium';
        detail.textContent = valueOf(field) || '—';
        item.append(term, detail);
        list.append(item);
      });

      section.append(heading, list);
      summary.append(section);
    });
  }

  function show(index, focus = true) {
    current = Math.max(0, Math.min(index, steps.length - 1));
    const isLast = current === steps.length - 1;

    steps.forEach((step, i) => { step.hidden = i !== current; });
    goButtons.forEach((button, i) => {
      const marker = button.querySelector('.step');
      const state = i < current ? 'done' : (i === current ? 'current' : 'todo');
      if (marker) {
        marker.classList.remove('step-done', 'step-current', 'step-todo');
        marker.classList.add('step-' + state);
      }
      if (i === current) button.setAttribute('aria-current', 'step');
      else button.removeAttribute('aria-current');
    });
    if (prev) prev.hidden = current === 0;
    if (next) next.hidden = isLast;
    if (submit) submit.hidden = !isLast;
    if (status) status.textContent = __t('step_of', { current: current + 1, total: steps.length, name: steps[current].dataset.step });
    if (steps[current].hasAttribute('data-stepper-review')) renderSummary();

    if (focus) {
      form.scrollIntoView({ block: 'start', behavior: 'smooth' });
      const heading = steps[current].querySelector('h2, h3');
      if (heading) {
        heading.setAttribute('tabindex', '-1');
        heading.classList.add('focus:outline-none');
        heading.focus({ preventScroll: true });
      }
    }
  }

  /* Move forward only through valid steps; stop on the first invalid field and show the browser's message. */
  function advanceTo(target) {
    for (let i = current; i < target; i++) {
      const invalid = firstInvalid(steps[i]);
      if (invalid) {
        if (i !== current) show(i);
        invalid.reportValidity();
        return false;
      }
    }
    show(target);
    return true;
  }

  /* Fields in hidden steps can't show the browser's messages, so steps are checked here instead. */
  form.noValidate = true;
  if (nav) nav.hidden = false;
  if (prev) prev.addEventListener('click', () => show(current - 1));
  if (next) next.addEventListener('click', () => advanceTo(current + 1));
  goButtons.forEach((button, i) => button.addEventListener('click', () => (i <= current ? show(i) : advanceTo(i))));

  /* Enter in a field moves to the next step instead of submitting early; on the last step, check every step. */
  form.addEventListener('submit', (event) => {
    if (current < steps.length - 1) {
      event.preventDefault();
      advanceTo(current + 1);
      return;
    }
    const broken = steps.findIndex((step) => firstInvalid(step));
    if (broken !== -1) {
      event.preventDefault();
      show(broken);
      firstInvalid(steps[broken]).reportValidity();
    }
  });

  form._stepper = { show };
  const withError = steps.findIndex(hasServerError);
  show(withError === -1 ? 0 : withError, withError !== -1);
}

function initSteppers(scope = document) {
  scope.querySelectorAll('form[data-stepper]').forEach(initStepper);
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initSteppers());
else initSteppers();
window.Stepper = { init: initSteppers };
