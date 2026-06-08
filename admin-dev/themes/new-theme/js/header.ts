/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
import refreshNotifications from '@js/notifications';
import ConfirmModal from '@components/modal';

const {$} = window;

export default class Header {
  constructor() {
    $(() => {
      this.initQuickAccess();
      this.initMultiStores();
      this.initNotificationsToggle();
      this.initSearch();
      refreshNotifications();
    });
  }

  initQuickAccess(): void {
    $(document).on('click', '.js-quick-link', (e) => {
      e.preventDefault();

      const $link = $(e.target).closest('.js-quick-link');
      const method = $link.data('method');
      let name = null;

      if (method === 'add') {
        const text = $link.data('prompt-text');
        const link = $link.data('link');

        name = prompt(text, link);
      }

      if (method === 'remove') {
        const confirmModal = new ConfirmModal(
          {
            id: 'quick-access-remove-confirm-modal',
            confirmTitle: $link.data('confirm-title'),
            confirmMessage: $link.data('confirm-message'),
            confirmButtonLabel: $link.data('confirm-button-label'),
            closeButtonLabel: $link.data('close-button-label'),
            confirmButtonClass: 'btn-danger',
          },
          () => this.doQuickLinkAction($link, method, null),
        );
        confirmModal.show();

        return;
      }

      if (method === 'add' && name) {
        this.doQuickLinkAction($link, method, name);
      }
    });
  }

  private doQuickLinkAction($link: JQuery, method: string, name: string | null): void {
    const postLink = $link.data('post-link');
    const quickLinkId = $link.data('quicklink-id');
    const url = $link.data('url');
    const icon = $link.data('icon');

    $.ajax({
      type: 'POST',
      headers: {
        'cache-control': 'no-cache',
      },
      async: true,
      url: postLink,
      data: {
        method,
        url,
        name,
        icon,
        id_quick_access: quickLinkId,
      },
      dataType: 'json',
      success: (data) => {
        if (typeof data.has_errors !== 'undefined' && data.has_errors) {
          $.each(data, (index) => {
            if (typeof data[index] === 'string') {
              $.growl.error({
                title: '',
                message: data[index],
              });
            }
          });
        } else if (Array.isArray(data)) {
          let quicklinkList = '';
          data.forEach((item) => {
            const classAttr = item.class ? ` ${item.class}` : '';
            const activeClass = item.active ? ' active' : '';
            const target = item.new_window ? ' target="_blank"' : '';
            quicklinkList += `<a class="dropdown-item quick-row-link${classAttr}${activeClass}"`
              + ` href="${item.link}"${target} data-item="${item.name}">${item.name}</a>`;
          });
          const $menu = $('#quick-access-container .dropdown-menu');
          $menu.find('.dropdown-divider').prevAll('a.quick-row-link').remove();
          $menu.prepend(quicklinkList);
          $link.remove();
          window.showSuccessMessage(window.update_success_msg);
        }
      },
      error: (xhr, textStatus) => {
        $.growl.error({
          title: 'Quick access error',
          message: textStatus === 'parsererror'
            ? `Server returned non-JSON (status ${xhr.status})`
            : `${xhr.status} ${xhr.statusText}`,
        });
      },
    });
  }

  initMultiStores(): void {
    $('.js-link').on('click', (e) => {
      window.open(
        $(e.target)
          .parents('.link')
          .attr('href'),
        '_blank',
      );
    });
  }

  initNotificationsToggle(): void {
    $('.notification.dropdown-toggle').on('click', () => {
      if (!$('.mobile-nav').hasClass('expanded')) {
        this.updateEmployeeNotifications();
      }
    });

    $('body').on('click', (e) => {
      if (
        !$('div.notification-center.dropdown').is(e.target)
        && $('div.notification-center.dropdown').has(e.target).length === 0
        && $('.open').has(e.target).length === 0
      ) {
        if ($('div.notification-center.dropdown').hasClass('open')) {
          $('.mobile-layer').removeClass('expanded');
          refreshNotifications();
        }
      }
    });

    $('.notification-center .nav-link').on('shown.bs.tab', () => {
      this.updateEmployeeNotifications();
    });
  }

  initSearch(): void {
    $('.js-items-list').on('click', (e) => {
      $('.js-form-search').attr('placeholder', $(e.target).data('placeholder'));
      $('.js-search-type').val($(e.target).data('value'));
      $('.js-dropdown-toggle').text($(e.target).data('item'));
    });
  }

  updateEmployeeNotifications(): void {
    $.post(window.adminNotificationPushLink, {
      type: $('.notification-center .nav-link.active').attr('data-type'),
    });
  }
}
