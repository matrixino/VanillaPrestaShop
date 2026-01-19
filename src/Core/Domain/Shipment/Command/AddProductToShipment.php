<?php
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

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\Shipment\Command;

use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Domain\Product\Combination\ValueObject\CombinationId;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use PrestaShop\PrestaShop\Core\Domain\Shipment\ValueObject\ShipmentId;

class AddProductToShipment
{
    /** @var ShipmentId */
    private $shipmentId;

    /** @var ProductId */
    private $productId;

    /** @var OrderId */
    private $orderId;

    /** @var ?CombinationId */
    private $combinationId;

    public function __construct(int $shipmentId, int $productId, int $orderId, ?int $combinationId = null)
    {
        $this->shipmentId = new ShipmentId($shipmentId);
        $this->productId = new ProductId($productId);
        $this->orderId = new OrderId($orderId);
        $this->combinationId = new CombinationId($combinationId);
    }

    public function getShipmentId(): ShipmentId
    {
        return $this->shipmentId;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getCombinationId(): ?CombinationId
    {
        return $this->combinationId;
    }
}
