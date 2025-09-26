/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
import Router from '@js/components/router';
import OrderViewPageMap from './OrderViewPageMap';

export default class EditShipmentManager {
  private formRoute = 'admin_orders_shipment_get_edit_form';

  private shipmentId: number|null = null;

  private orderId: number|null = null;

  private router = new Router();

  constructor() {
    this.initEditShipmentEventHandler();
  }

  initEditShipmentEventHandler(): void {
    const mainDiv = document.querySelector(OrderViewPageMap.mainDiv);

    if (!mainDiv) {
      throw new Error(
        `Initialization failed: main container not found for selector "${
          OrderViewPageMap.mainDiv
        }". The shipment edit feature cannot be initialized.`,
      );
    }
    mainDiv.addEventListener('click', this.onEditShipmentClick);
  }

  onEditShipmentClick = (event: Event): void => {
    const link = (event.target as HTMLElement).closest<HTMLAnchorElement>(OrderViewPageMap.showEditShipmentModalBtn);

    if (!link) {
      return;
    }

    const {orderId, shipmentId} = link.dataset;

    if (!orderId || !shipmentId) {
      throw new Error('error while gettint orderId or shipmentId');
    }

    this.orderId = Number(orderId);
    this.shipmentId = Number(shipmentId);

    this.refreshEditShipmentForm();
  };

  async refreshEditShipmentForm(): Promise<void> {
    const modal = document.querySelector(OrderViewPageMap.editShipmentModal) as HTMLElement;

    if (!modal) {
      throw new Error('Edit shipment modal not found.');
    }

    modal.dataset.state = 'loading';

    try {
      const response = await fetch(this.router.generate(this.formRoute, {
        orderId: this.orderId,
        shipmentId: this.shipmentId,
      }), {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(await response.text());
      }
      const formContainer = document.querySelector(OrderViewPageMap.editShipmentModalContainer) as HTMLElement;
      formContainer!.innerHTML = await response.text();

      modal.dataset.state = 'loaded';

      window.prestaShopUiKit.init();
    } catch (error) {
      console.error('Error while loading edit shipment form:', error);
    }
  }
}
