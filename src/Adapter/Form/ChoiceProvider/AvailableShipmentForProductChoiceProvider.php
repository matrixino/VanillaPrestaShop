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

namespace PrestaShop\PrestaShop\Adapter\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Shipment\Query\GetOrderShipments;
use PrestaShop\PrestaShop\Core\Domain\Shipment\QueryResult\OrderShipment;
use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShop\PrestaShop\Core\Form\FormChoiceFormatter;
use PrestaShopBundle\Entity\Repository\ShipmentRepository;
use Product;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvailableShipmentForProductChoiceProvider implements ConfigurableFormChoiceProviderInterface
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly ShipmentRepository $shipmentRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(array $options): array
    {
        $options = $this->resolveOptions($options);
        $productInstance = new Product($options['product_id']);
        $orderId = $options['order_id'];

        $availableShipmentsForProductSelected = [];

        /** @var OrderShipment $orderShipments */
        $orderShipments = $this->commandBus->handle(new GetOrderShipments($orderId));

        foreach ($orderShipments as $shipment) {
            // productInstance->getCarriers() return empty array if product is handle by ALL carriers
            if (count($productInstance->getCarriers()) === 0) {
                $availableShipmentsForProductSelected[] = $shipment;
            }
            if (in_array($shipment->getCarrierSummary()->getId(), $productInstance->getCarriers())) {
                $availableShipmentsForProductSelected[] = $shipment;
            }
        }

        $formattedShipments = array_map(function ($shipment) {
            return [
                'id' => $shipment->getId(),
                'name' => 'Shipment ' . $shipment->getId(),
            ];
        }, $availableShipmentsForProductSelected);

        return FormChoiceFormatter::formatFormChoices(
            $formattedShipments,
            'id',
            'name'
        );
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'product_id',
            'order_id',
        ]);

        return $resolver->resolve($options);
    }
}
