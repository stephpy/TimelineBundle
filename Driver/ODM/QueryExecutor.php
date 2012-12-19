<?php

namespace Spy\TimelineBundle\Driver\ODM;

use Spy\Timeline\ResultBuilder\QueryExecutor\QueryExecutorInterface;
use Doctrine\ODM\MongoDB\Query\Builder;

/**
 * QueryExecutor
 *
 * @uses QueryExecutorInterface
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class QueryExecutor implements QueryExecutorInterface
{

    /**
     * {@inheritdoc}
     */
    public function fetch($target, $page = 1, $limit = 10)
    {
        if (!$target instanceof Builder) {
            throw new \Exception('Not supported yet');
        }

        $clone = clone $target;
        if ($limit) {
            $skip = $limit * ($page - 1);

            $target
                ->skip($skip)
                ->limit($limit);
        }

        return $target->getQuery()
            ->execute();
    }
}
