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
 * B2bRole.
 *
 * @ORM\Table(name="PREFIX_b2b_role", indexes={
 *
 *     @ORM\Index(name="uniq_b2b_role", columns={"role"})
 * })
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
    private int $idRole;

    /**
     * @ORM\Column(name="role", type="string", length=64)
     */
    private string $role;

    // Getters and setters

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
}
