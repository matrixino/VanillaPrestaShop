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

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessEntity.
 *
 * @ORM\Table(name="PREFIX_business_entity", indexes={
 *
 *     @ORM\Index(name="business_entity_enterprise_id_idx", columns={"enterprise_id"}),
 *     @ORM\Index(name="business_entity_external_ref_idx", columns={"external_ref"})
 * })
 *
 * @ORM\Entity()
 */
class BusinessEntity
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id_business_entity", type="integer", options={"unsigned"=true})
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(name="enterprise_id", type="string", length=255)
     */
    private string $enterpriseId;

    /**
     * @ORM\Column(name="external_ref", type="string", length=255, nullable=true)
     */
    private ?string $externalRef;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(name="legal_name", type="string", length=255, nullable=true)
     */
    private ?string $legalName;

    /**
     * @ORM\Column(name="flag_delivery_authorized", type="boolean", options={"default"=false})
     */
    private bool $flagDeliveryAuthorized = false;

    /**
     * @ORM\Column(name="status", type="string", length=50, columnDefinition="ENUM('pending', 'active', 'inactive', 'rejected') DEFAULT 'pending'")
     */
    private string $status = 'pending';

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private DateTime $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private DateTime $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEnterpriseId(): string
    {
        return $this->enterpriseId;
    }

    public function getExternalRef(): ?string
    {
        return $this->externalRef;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function isFlagDeliveryAuthorized(): bool
    {
        return $this->flagDeliveryAuthorized;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setEnterpriseId(string $enterpriseId): self
    {
        $this->enterpriseId = $enterpriseId;

        return $this;
    }

    public function setExternalRef(?string $externalRef): self
    {
        $this->externalRef = $externalRef;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setLegalName(?string $legalName): self
    {
        $this->legalName = $legalName;

        return $this;
    }

    public function setFlagDeliveryAuthorized(bool $flagDeliveryAuthorized): self
    {
        $this->flagDeliveryAuthorized = $flagDeliveryAuthorized;

        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
