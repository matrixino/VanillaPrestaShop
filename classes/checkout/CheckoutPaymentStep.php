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
use PrestaShop\PrestaShop\Adapter\ContainerFinder;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagSettings;
use PrestaShop\PrestaShop\Core\FeatureFlag\FeatureFlagStateCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckoutPaymentStepCore extends AbstractCheckoutStep
{
    protected $template = 'checkout/_partials/steps/payment.tpl';
    private $selected_payment_option;

    /**
     * @var ConditionsToApproveFinder
     */
    public $conditionsToApproveFinder;

    /**
     * @var PaymentOptionsFinder
     */
    public $paymentOptionsFinder;

    /**
     * @param Context $context
     * @param TranslatorInterface $translator
     * @param PaymentOptionsFinder $paymentOptionsFinder
     * @param ConditionsToApproveFinder $conditionsToApproveFinder
     */
    public function __construct(
        Context $context,
        TranslatorInterface $translator,
        PaymentOptionsFinder $paymentOptionsFinder,
        ConditionsToApproveFinder $conditionsToApproveFinder,
    ) {
        parent::__construct($context, $translator);
        $this->paymentOptionsFinder = $paymentOptionsFinder;
        $this->conditionsToApproveFinder = $conditionsToApproveFinder;
    }

    public function handleRequest(array $requestParams = [])
    {
        if (isset($requestParams['select_payment_option'])) {
            $this->selected_payment_option = $requestParams['select_payment_option'];
        }

        $this->setTitle(
            $this->getTranslator()->trans(
                'Payment',
                [],
                'Shop.Theme.Checkout'
            )
        );
    }

    /**
     * @param array $extraParams
     *
     * @return string
     */
    public function render(array $extraParams = [])
    {
        $isFree = 0 == (float) $this->getCheckoutSession()->getCart()->getOrderTotal(true, Cart::BOTH);
        $paymentOptions = $this->paymentOptionsFinder->present($isFree);
        $conditionsToApprove = $this->conditionsToApproveFinder->getConditionsToApproveForTemplate();
        $deliveryOptions = $this->getCheckoutSession()->getDeliveryOptions();
        $deliveryOptionKey = $this->getCheckoutSession()->getSelectedDeliveryOption();

        if (isset($deliveryOptions[$deliveryOptionKey])) {
            $selectedDeliveryOption = $deliveryOptions[$deliveryOptionKey];
        } else {
            $selectedDeliveryOption = 0;
        }

        if (true === is_array($selectedDeliveryOption) && isset($selectedDeliveryOption['product_list'])) {
            unset($selectedDeliveryOption['product_list']);
        }

        $containerFinder = new ContainerFinder($this->context);
        /** @var FeatureFlagStateCheckerInterface $featureFlagManager */
        $featureFlagManager = $containerFinder->getContainer()->get(FeatureFlagStateCheckerInterface::class);

        $productsCarrierMapping = $this->getCheckoutSession()->getProductsByCarrier();
        $deliveryOptionKeys = array_filter(explode(',', $deliveryOptionKey));
        $productsCarrierMapping = array_intersect_key($productsCarrierMapping, array_flip($deliveryOptionKeys));
        $mapping = [
            'physical_products' => [],
            'virtual_products' => [],
        ];

        foreach ($productsCarrierMapping as $product) {
            if (!empty($product['physical_products'])) {
                $mapping['physical_products'][] = $product['physical_products'];
            }
            if (!empty($product['virtual_products'])) {
                $mapping['virtual_products'] = $product['virtual_products'];
            }
        }

        $assignedVars = [
            'is_free' => $isFree,
            'payment_options' => $paymentOptions,
            'conditions_to_approve' => $conditionsToApprove,
            'selected_payment_option' => $this->selected_payment_option,
            'selected_delivery_option' => $selectedDeliveryOption,
            'show_final_summary' => Configuration::get('PS_FINAL_SUMMARY_ENABLED'),
            'is_multishipment_enabled' => $featureFlagManager->isEnabled(FeatureFlagSettings::FEATURE_FLAG_IMPROVED_SHIPMENT),
            'products_carrier_mapping' => $mapping,
            'is_recyclable_packaging' => $this->getCheckoutSession()->isRecyclable(),
        ];

        return $this->renderTemplate($this->getTemplate(), $extraParams, $assignedVars);
    }
}
