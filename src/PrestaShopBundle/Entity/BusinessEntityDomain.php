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
 * BusinessEntityDomain.
 *
 * @ORM\Table(name="PREFIX_business_entity_domain", indexes={
 *
 *     @ORM\Index(name="business_entity_domain_id_business_entity_idx", columns={"id_business_entity"}),
 *     @ORM\Index(name="business_entity_domain_id_business_identifier_idx", columns={"id_business_identifier"}),
 *     @ORM\Index(name="business_entity_domain_value_idx", columns={"value"})
 * }, uniqueConstraints={
 *
 *     @ORM\UniqueConstraint(name="uniq_business_entity_domain", columns={"id_business_entity", "id_business_identifier"})
 * })
 *
 * @ORM\Entity()
 */
class BusinessEntityDomain
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id_domain", type="integer", options={"unsigned"=true})
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $idDomain;

    /**
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\BusinessEntity", inversedBy="domains")
     *
     * @ORM\JoinColumn(name="id_business_entity", referencedColumnName="id_business_entity", nullable=false)
     */
    private BusinessEntity $businessEntity;

    /**
     * @ORM\Column(name="id_business_identifier", type="integer", options={"unsigned"=true})
     */
    private int $businessIdentifierId;

    /**
     * @ORM\Column(name="value", type="string", length=255)
     */
    private string $value;

    public function getIdDomain(): int
    {
        return $this->idDomain;
    }

    public function getBusinessEntity(): BusinessEntity
    {
        return $this->businessEntity;
    }

    public function getBusinessIdentifierId(): int
    {
        return $this->businessIdentifierId;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setBusinessEntity(BusinessEntity $businessEntity): self
    {
        $this->businessEntity = $businessEntity;

        return $this;
    }

    public function setBusinessIdentifierId(int $businessIdentifierId): self
    {
        $this->businessIdentifierId = $businessIdentifierId;

        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }
}
