/*
 * Fills read-only fields from a Malaysian NRIC number as it's typed. Markup, inside one form:
 *   <input data-nric>                                   12 digits, YYMMDD + 6 digits; other characters are dropped
 *   <input data-nric-fill="date_of_birth" readonly>     type="date", gets YYYY-MM-DD
 *   <input data-nric-fill="gender" data-nric-labels='{"male":"Male","female":"Female"}' readonly>
 * Mirrors App\Support\MyKad::birthDate() and ::gender(): a two-digit year later than this year belongs to
 * the previous century, and an odd last digit is male. The server works both out again when saving.
 */
function fromNric(value) {
  const match = /^(\d{2})(\d{2})(\d{2})\d{5}(\d)$/.exec(value);
  if (!match) return null;

  const thisYear = new Date().getFullYear();
  let year = 2000 + Number(match[1]);
  if (year > thisYear) year -= 100;
  const month = Number(match[2]);
  const day = Number(match[3]);
  const date = new Date(Date.UTC(year, month - 1, day));
  if (date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) return null;

  const pad = (number) => String(number).padStart(2, '0');
  return {
    date_of_birth: year + '-' + pad(month) + '-' + pad(day),
    gender: Number(match[4]) % 2 === 1 ? 'male' : 'female',
  };
}

function fillFromNric(input) {
  const scope = input.form || document;
  const details = fromNric(input.value.trim());

  scope.querySelectorAll('[data-nric-fill]').forEach((field) => {
    const key = field.dataset.nricFill;
    let value = details ? details[key] : '';
    if (key === 'gender' && field.dataset.nricLabels) {
      try { value = JSON.parse(field.dataset.nricLabels)[value] || ''; } catch { value = ''; }
    }
    if (field.value !== value) field.value = value;
  });
}

/* Keep digits only (typed or pasted, so 960109-04-5295 becomes 960109045295), at most 12. */
function keepDigits(input) {
  const digits = input.value.replace(/\D/g, '').slice(0, 12);
  if (input.value === digits) return;

  const caret = input.selectionStart === null ? digits.length : input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;
  input.value = digits;
  try { input.setSelectionRange(caret, caret); } catch { /* not supported on this input type */ }
}

document.addEventListener('input', (event) => {
  if (!event.target.matches || !event.target.matches('[data-nric]')) return;
  keepDigits(event.target);
  fillFromNric(event.target);
});

function initNric(scope = document) {
  scope.querySelectorAll('[data-nric]').forEach(fillFromNric);
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initNric());
else initNric();
