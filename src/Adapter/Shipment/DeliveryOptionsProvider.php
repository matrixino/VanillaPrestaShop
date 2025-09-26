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

namespace PrestaShop\PrestaShop\Adapter\Shipment;

use Context;
use DeliveryOptionsFinderCore;
use Hook;
use Module;
use PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeliveryOptionsProvider extends DeliveryOptionsFinderCore
{
    private $context;

    public function __construct(
        Context $context,
        TranslatorInterface $translator,
        ObjectPresenter $objectPresenter,
        PriceFormatter $priceFormatter
    ) {
        parent::__construct($context, $translator, $objectPresenter, $priceFormatter);
        $this->context = $context;
    }

    public function getDeliveryOptions()
    {
        $parentOutput = parent::getDeliveryOptions();

        $deliveryOptions = $this->context->cart->getDeliveryOptionList();
        $currentAddressDeliveryOptions = $deliveryOptions[$this->context->cart->id_address_delivery];

        foreach ($currentAddressDeliveryOptions as $deliveryOption) {
            $carrierIdsAsString = implode(',', array_keys($deliveryOption['carrier_list'])) . ',';
            $carriersDetails = $this->getCarriersDetails($deliveryOption);

            // override parent output to mention all the carrier within the delivery option
            if (!empty($carriersDetails)) {
                $parentOutput[$carrierIdsAsString]['name'] = $carriersDetails['name'];
                $parentOutput[$carrierIdsAsString]['delay'] = $carriersDetails['delay'];
                $parentOutput[$carrierIdsAsString]['extraContent'] = $carriersDetails['extraContent'];
            }
        }

        return $parentOutput;
    }

    private function getCarriersDetails(array $deliveryOption): array
    {
        $carriers = $deliveryOption['carrier_list'];

        if (count($carriers) === 1) {
            return [];
        }

        $names = [];
        $delays = [];
        $extraContent = '';

        // If carrier related to a module, check for additionnal data to display
        foreach ($carriers as $carrier) {
            $names[] = $carrier['instance']->name;
            $delays[] = $carrier['instance']->delay[$this->context->language->id];

            // if more than on carrier are in the same delivery options then concatenate
            // all extracontent
            $extraContent .= Hook::exec('displayCarrierExtraContent', ['carrier' => $carrier['instance']], Module::getModuleIdByName($carrier['instance']->id));
        }

        return [
            'name' => implode(', ', $names),
            'delay' => implode(', ', $delays),
            'extraContent' => $extraContent,
        ];
    }
}
