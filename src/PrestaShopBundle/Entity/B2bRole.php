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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * B2bRole.
 *
 * @ORM\Table(
 *     indexes={@ORM\Index(name="uniq_b2b_role", columns={"role"})}
 * )
 *
 * @ORM\Entity()
 */
class B2bRole
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id_role", type="integer", options={"unsigned"=true})
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="role", type="string", length=64)
     */
    private string $role;

    /**
     * @ORM\OneToMany(targetEntity="PrestaShopBundle\Entity\BusinessEntityCustomerB2b", mappedBy="roleB2b")
     */
    private Collection $businessEntityCustomerB2bs;

    /**
     * @ORM\OneToMany(targetEntity="PrestaShopBundle\Entity\B2bRoleAuthorizationRole", mappedBy="role")
     */
    private Collection $b2bRoleAuthorizationRoles;

    public function __construct()
    {
        $this->businessEntityCustomerB2bs = new ArrayCollection();
        $this->b2bRoleAuthorizationRoles = new ArrayCollection();
    }

    public function getIdRole(): int
    {
        return $this->idRole;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getBusinessEntityCustomerB2bs(): Collection
    {
        return $this->businessEntityCustomerB2bs;
    }

    public function addBusinessEntityCustomerB2b(BusinessEntityCustomerB2b $businessEntityCustomerB2b): self
    {
        if (!$this->businessEntityCustomerB2bs->contains($businessEntityCustomerB2b)) {
            $this->businessEntityCustomerB2bs[] = $businessEntityCustomerB2b;
            $businessEntityCustomerB2b->setRoleB2b($this);
        }

        return $this;
    }

    public function removeBusinessEntityCustomerB2b(BusinessEntityCustomerB2b $businessEntityCustomerB2b): self
    {
        if ($this->businessEntityCustomerB2bs->contains($businessEntityCustomerB2b)) {
            $this->businessEntityCustomerB2bs->removeElement($businessEntityCustomerB2b);
            if ($businessEntityCustomerB2b->getRoleB2b() === $this) {
                $businessEntityCustomerB2b->setRoleB2b(null);
            }
        }

        return $this;
    }

    public function getB2bRoleAuthorizationRoles(): Collection
    {
        return $this->b2bRoleAuthorizationRoles;
    }

    public function addB2bRoleAuthorizationRole(B2bRoleAuthorizationRole $b2bRoleAuthorizationRole): self
    {
        if (!$this->b2bRoleAuthorizationRoles->contains($b2bRoleAuthorizationRole)) {
            $this->b2bRoleAuthorizationRoles[] = $b2bRoleAuthorizationRole;
            $b2bRoleAuthorizationRole->setRole($this);
        }

        return $this;
    }

    public function removeB2bRoleAuthorizationRole(B2bRoleAuthorizationRole $b2bRoleAuthorizationRole): self
    {
        if ($this->b2bRoleAuthorizationRoles->contains($b2bRoleAuthorizationRole)) {
            $this->b2bRoleAuthorizationRoles->removeElement($b2bRoleAuthorizationRole);
            if ($b2bRoleAuthorizationRole->getRole() === $this) {
                $b2bRoleAuthorizationRole->setRole(null);
            }
        }

        return $this;
    }
}
