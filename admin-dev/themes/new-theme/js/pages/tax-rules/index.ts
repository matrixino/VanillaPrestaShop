/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

import FormSubmitButton from '@components/form-submit-button';
import IframeModal from '@components/modal/iframe-modal';

const {$} = window;

const TAX_RULE_MODAL_ID = 'tax-rule-form-modal';

$(() => {
  const taxRuleGrid = new window.prestashop.component.Grid('tax_rules');

  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.SortingExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension());
  taxRuleGrid.addExtension(new window.prestashop.component.GridExtensions.ColumnTogglingExtension());

  new FormSubmitButton();
  initTaxRuleModals();
});

/**
 * Intercept add and edit tax rule links to open them in a modal.
 */
function initTaxRuleModals(): void {
  // "Add new tax rule" toolbar button
  document.querySelectorAll<HTMLAnchorElement>('[href*="tax-rules/new"]').forEach((button) => {
    button.addEventListener('click', (event: MouseEvent) => {
      event.preventDefault();
      const href = button.getAttribute('href');

      if (href) {
        openTaxRuleModal(`${href}${href.includes('?') ? '&' : '?'}liteDisplaying=1`);
      }
    });
  });

  // Edit links in grid rows
  document.querySelectorAll<HTMLAnchorElement>('#tax_rules_grid_table a.btn[href*="/edit"]').forEach((link) => {
    link.addEventListener('click', (event: MouseEvent) => {
      event.preventDefault();
      event.stopPropagation();
      const href = link.getAttribute('href');

      if (href) {
        openTaxRuleModal(`${href}${href.includes('?') ? '&' : '?'}liteDisplaying=1`);
      }
    });
  });
}

function openTaxRuleModal(formUrl: string): void {
  const iframeModal = new IframeModal({
    id: TAX_RULE_MODAL_ID,
    iframeUrl: formUrl,
    closable: true,
    onLoaded: (iframe: HTMLIFrameElement): void => {
      if (!iframe.contentWindow) {
        return;
      }

      const iframeDoc = iframe.contentWindow.document;

      // Check for success marker — controller returns <div data-modal-close="true"> after save
      const closeMarker = iframeDoc.querySelector('[data-modal-close]');

      if (closeMarker) {
        // Save succeeded — close modal and reload parent to show flash message + updated grid
        iframeModal.hide();
        window.location.reload();

        return;
      }

      // Form page loaded — wire cancel button to close modal
      const cancelButtons = iframeDoc.querySelectorAll('.cancel-btn');
      cancelButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          iframeModal.hide();
        });
      });
    },
  });
  iframeModal.show();
}
