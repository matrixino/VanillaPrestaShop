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
 * BusinessEntityAddress.
 *
 * @ORM\Table(name="PREFIX_business_entity_address", indexes={
 *
 *     @ORM\Index(name="business_entity_address_address_idx", columns={"id_address"})
 * })
 *
 * @ORM\Entity()
 */
class BusinessEntityAddress
{
    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\BusinessEntity")
     *
     * @ORM\JoinColumn(name="id_business_entity", referencedColumnName="id_business_entity", nullable=false)
     */
    private BusinessEntity $businessEntity;

    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\Address")
     *
     * @ORM\JoinColumn(name="id_address", referencedColumnName="id_address", nullable=false)
     */
    private Address $address;

    /**
     * @ORM\Column(name="address_type", type="string", length=50, columnDefinition="ENUM('both', 'invoice', 'delivery') DEFAULT 'both'")
     */
    private string $addressType = 'both';

    public function getBusinessEntity(): BusinessEntity
    {
        return $this->businessEntity;
    }

    public function setBusinessEntity(BusinessEntity $businessEntity): self
    {
        $this->businessEntity = $businessEntity;

        return $this;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressType(): string
    {
        return $this->addressType;
    }

    public function setAddressType(string $addressType): self
    {
        $this->addressType = $addressType;

        return $this;
    }
}
