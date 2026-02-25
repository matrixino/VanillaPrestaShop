<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Controller\Admin\Sell\BusinessEntity;

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BusinessEntitiesControllerr manages "Sell > Business Entities" page.
 */
class BusinessEntitiesController extends PrestaShopAdminController
{
    /**
     * @return Response
     */
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function listAction(): Response
    {
        return $this->render('@PrestaShop/Admin/Sell/BusinessEntity/list.html.twig');
    }
}
