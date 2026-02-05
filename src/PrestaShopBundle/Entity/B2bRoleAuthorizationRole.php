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

namespace PrestaShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * B2bRoleAuthorizationRole.
 *
 * @ORM\Table(name="PREFIX_b2b_role_authorization_role", indexes={
 *
 *     @ORM\Index(name="b2b_role_authorization_role_role_idx", columns={"id_role"}),
 *     @ORM\Index(name="b2b_role_authorization_role_auth_role_idx", columns={"id_authorization_role"})
 * })
 *
 * @ORM\Entity()
 */
class B2bRoleAuthorizationRole
{
    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\B2bRole")
     *
     * @ORM\JoinColumn(name="id_role", referencedColumnName="id_role", nullable=false)
     */
    private B2bRole $role;

    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\AuthorizationRole") // Si tu as une entité AuthorizationRole, sinon à remplacer
     *
     * @ORM\JoinColumn(name="id_authorization_role", referencedColumnName="id_authorization_role", nullable=false)
     */
    private AuthorizationRole $authorizationRole;

    public function getRole(): B2bRole
    {
        return $this->role;
    }

    public function setRole(B2bRole $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getAuthorizationRole(): AuthorizationRole
    {
        return $this->authorizationRole;
    }

    public function setAuthorizationRole(AuthorizationRole $authorizationRole): self
    {
        $this->authorizationRole = $authorizationRole;

        return $this;
    }
}
