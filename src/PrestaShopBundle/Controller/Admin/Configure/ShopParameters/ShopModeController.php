<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SOLEDIS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SOLEDIS GROUP is strictly forbidden.
 * ___ ___ _ ___ ___ ___ ___
 * / __|/ _ \| | | __| \_ _/ __|
 * \__ \ (_) | |__| _|| |) | |\__ \
 * |___/\___/|____|___|___/___|___/
 *
 * @author    SOLEDIS <prestashop@groupe-soledis.com>
 * @copyright 2025 SOLEDIS
 * @license   All Rights Reserved
 * @developer HERVOUET Clément
 */

namespace PrestaShopBundle\Controller\Admin\Configure\ShopParameters;

use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use PrestaShopBundle\Security\Attribute\DemoRestricted;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopModeController extends PrestaShopAdminController
{
    public const CONTROLLER_NAME = 'AdminShopModes';

    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function indexAction(
        #[Autowire(service: 'prestashop.admin.shop_modes.form_handler')]
        FormHandlerInterface $shopModesFormHandler,
    ): Response {
        $form = $shopModesFormHandler->getForm();

        return $this->render('@PrestaShop/Admin/Configure/ShopParameters/shop_modes.html.twig', [
            'layoutHeaderToolbarBtn' => [],
            'layoutTitle' => $this->trans('Shop modes', [], 'Admin.Navigation.Menu'),
            'requireBulkActions' => false,
            'showContentHeader' => true,
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink(self::CONTROLLER_NAME),
            'requireFilterStatus' => false,
            'generalForm' => $form->createView(),
        ]);
    }

    #[DemoRestricted(redirectRoute: 'prestashop.admin.shop_modes.form_handler')]
    #[AdminSecurity("is_granted('update', request.get('_legacy_controller')) && is_granted('create', request.get('_legacy_controller')) && is_granted('delete', request.get('_legacy_controller'))", message: 'You do not have permission to edit this.', redirectRoute: 'admin_shop_modes')]
    public function processFormAction(
        Request $request,
        #[Autowire(service: 'prestashop.admin.shop_modes.form_handler')]
        FormHandlerInterface $shopModesFormHandler,
    ): RedirectResponse {
        $redirectResponse = $this->redirectToRoute('admin_shop_modes');
        $form = $shopModesFormHandler->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        $data = $form->getData();
        $saveErrors = $shopModesFormHandler->save($data);

        if (0 === count($saveErrors)) {
            $this->addFlash('success', $this->trans('Successful update', [], 'Admin.Notifications.Success'));

            return $redirectResponse;
        }

        $this->addFlashErrors($saveErrors);

        return $redirectResponse;
    }
}
