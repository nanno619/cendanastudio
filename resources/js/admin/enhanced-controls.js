import Choices from 'choices.js';
import flatpickr from 'flatpickr';
import { Malaysian } from 'flatpickr/dist/l10n/ms.js';

const isMalay = document.documentElement.lang === 'ms';

function initControls(scope = document) {
  scope.querySelectorAll('[data-enhanced-select]').forEach((select) => {
    if (select._cendanaChoices) return;
    select._cendanaChoices = new Choices(select, {
      searchEnabled: true,
      searchPlaceholderValue: __t('select_search'),
      itemSelectText: '',
      shouldSort: false,
      allowHTML: false,
      noResultsText: __t('select_no_results'),
      noChoicesText: __t('select_no_choices'),
      placeholder: true,
      placeholderValue: select.dataset.placeholder || __t('select_placeholder'),
    });
  });

  scope.querySelectorAll('[data-date-picker]').forEach((input) => {
    if (input._flatpickr) return;
    const picker = flatpickr(input, {
      locale: isMalay ? Malaysian : 'default',
      dateFormat: 'Y-m-d',
      altInput: true,
      altInputClass: 'input cendana-date-display',
      altFormat: 'j F Y',
      allowInput: false,
      disableMobile: true,
    });
    if (picker.altInput && input.id) {
      picker.altInput.id = input.id;
      input.id += '-value';
    }
  });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initControls());
else initControls();
window.EnhancedControls = { init: initControls };
