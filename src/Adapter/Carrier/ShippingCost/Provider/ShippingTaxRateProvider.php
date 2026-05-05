<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Carrier\ShippingCost\Provider;

use PrestaShop\PrestaShop\Adapter\Address\Repository\AddressRepository;
use PrestaShop\PrestaShop\Adapter\Carrier\Repository\CarrierRepository;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ShippingCost\Provider\ShippingTaxRateProviderInterface;
use PrestaShop\PrestaShop\Core\Domain\Carrier\ValueObject\CarrierId;

class ShippingTaxRateProvider implements ShippingTaxRateProviderInterface
{
    public function __construct(
        private readonly CarrierRepository $carrierRepository,
        private readonly AddressRepository $addressRepository,
    ) {
    }

    public function getTaxRate(int $carrierId, int $addressId): float
    {
        try {
            $carrier = $this->carrierRepository->get(new CarrierId($carrierId));
            $address = $this->addressRepository->get(new AddressId($addressId));

            return (float) $carrier->getTaxesRate($address);
        } catch (\Exception) {
            return 0.0;
        }
    }
}
