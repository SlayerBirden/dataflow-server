<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlowServer\Doctrine\Collection;

use Doctrine\Common\Collections\Criteria;

final class CriteriaBuilder
{
    const FILTERS = 'f';
    const PAGE = 'p';
    const SORTING = 's';
    const LIMIT = 'l';

    /**
     * Build Criteria based on query params
     *
     * @param array $data
     * @return Criteria
     */
    public function __invoke(
        array $data
    ): Criteria {
        $page = isset($data[self::PAGE]) ? abs($data[self::PAGE]) : 1;
        $limit = isset($data[self::LIMIT]) ? abs($data[self::LIMIT]) : 10;
        $filters = $data[self::FILTERS] ?? [];
        $sorting = $data[self::SORTING] ?? [];

        $criteria = Criteria::create();
        $criteria->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        foreach ($filters as $key => $value) {
            if (is_string($value)) {
                $criteria->andWhere(Criteria::expr()->contains($key, $value));
            } else {
                $criteria->andWhere(Criteria::expr()->eq($key, $value));
            }
        }
        if (!empty($sorting)) {
            foreach ($sorting as $key => $dir) {
                $criteria->orderBy($sorting);
            }
        }

        return $criteria;
    }
}
