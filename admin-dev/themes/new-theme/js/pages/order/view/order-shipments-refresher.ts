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

import Router from '@components/router';
import OrderViewPageMap from '@pages/order/OrderViewPageMap';

export default class OrderShipmentsRefresher {
  router: Router;

  constructor() {
    this.router = new Router();
  }

  refresh(orderId: number): void {
    fetch(this.router.generate('admin_orders_get_shipments', {orderId}))
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Unable to retrieve shipments for order ${orderId}`);
        }
        return response.json();
      })
      .then((response) => {
        const countElement = document.querySelector(OrderViewPageMap.orderShipmentsTabCount);

        if (countElement) {
          countElement.textContent = response.total;
        }
        const bodyElement = document.querySelector(OrderViewPageMap.orderShipmentsTabBody);

        if (bodyElement) {
          bodyElement.innerHTML = response.html;
        }
      });
  }
}
