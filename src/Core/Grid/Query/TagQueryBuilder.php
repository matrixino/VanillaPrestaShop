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

namespace PrestaShop\PrestaShop\Core\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Class TagQueryBuilder provides query builders for tags grid.
 */
class TagQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $searchCriteriaApplicator;

    /**
     * @param Connection $connection
     * @param string $dbPrefix
     * @param DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
     */
    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);

        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $builder = $this->getTagQueryBuilder($searchCriteria)
            ->select('t.*, num_products, l.name AS language');

        $this->searchCriteriaApplicator
            ->applySorting($searchCriteria, $builder)
            ->applyPagination($searchCriteria, $builder);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        return $this->getTagQueryBuilder($searchCriteria)->select('COUNT(DISTINCT t.id_tag)');
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return QueryBuilder
     */
    private function getTagQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $builder = $this->connection->createQueryBuilder()
            ->from($this->dbPrefix . 'tag', 't')
            ->leftJoin('t', $this->dbPrefix . 'lang', 'l', 't.id_lang = l.id_lang')
        ;

        $productCountQb = $this->connection
            ->createQueryBuilder()
            ->from($this->dbPrefix . 'product_tag', 'pt')
            ->select('pt.`id_tag`, COUNT(*) as num_products')
            ->groupBy('id_tag');

        $builder->leftJoin('t',
            '(' . $productCountQb->getSQL() . ')',
            'vpt',
            't.`id_tag` = vpt.`id_tag`');

        $this->applyFilters($builder, $searchCriteria);

        return $builder;
    }

    /**
     * @param QueryBuilder $builder
     * @param SearchCriteriaInterface $searchCriteria
     */
    private function applyFilters(QueryBuilder $builder, SearchCriteriaInterface $searchCriteria): void
    {
        $allowedFiltersMap = [
            'id_tag' => 't.id_tag',
            'id_lang' => 't.id_lang',
            'name' => 't.name',
            'num_products' => 'num_products',
        ];

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if (!array_key_exists($filterName, $allowedFiltersMap)) {
                continue;
            }

            if ($filterName === 'num_products') {
                $builder
                    ->andWhere($allowedFiltersMap[$filterName] . ' = :' . $filterName)
                    ->setParameter($filterName, $filterValue);

                continue;
            }

            $builder
                ->andWhere($allowedFiltersMap[$filterName] . ' LIKE :' . $filterName)
                ->setParameter($filterName, '%' . $filterValue . '%');
        }
    }
}
