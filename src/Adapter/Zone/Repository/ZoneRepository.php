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
declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Zone\Repository;

use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;
use PrestaShop\PrestaShop\Core\Domain\Zone\Exception\ZoneNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Zone\ValueObject\ZoneId;
use PrestaShop\PrestaShop\Core\Repository\AbstractObjectModelRepository;
use Zone;

/**
 * Provides methods to access data storage of Zone
 */
class ZoneRepository extends AbstractObjectModelRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $prefix,
    ) {
    }

    /**
     * @throws ZoneNotFoundException
     */
    public function get(ZoneId $zoneId): Zone
    {
        /** @var Zone $zone */
        $zone = $this->getObjectModel(
            $zoneId->getValue(),
            Zone::class,
            ZoneNotFoundException::class
        );

        return $zone;
    }

    /**
     * @throws ZoneNotFoundException
     */
    public function getZoneIdByCountryId(CountryId $countryId): ZoneId
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('z.id_zone')
            ->from($this->prefix . 'country', 'c')
            ->innerJoin(
                'c',
                $this->prefix . 'zone',
                'z',
                'z.id_zone = c.id_zone'
            )
            ->where('c.id_country = :countryId')
            ->setParameter('countryId', $countryId->getValue())
        ;

        $result = $qb->executeQuery()->fetchAssociative();

        if (!$result) {
            throw new ZoneNotFoundException(sprintf('Zone not found for country %d', $countryId->getValue()));
        }

        return new ZoneId((int) $result['id_zone']);
    }
}
