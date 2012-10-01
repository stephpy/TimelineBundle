<?php

namespace Highco\TimelineBundle\Filter;

/**
 * This interface define how filters must be used
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
interface FilterInterface
{
    /**
     * @param  array $options options
     * @return void
     */
    public function initialize(array $options = array());

    /**
     * This action will filters each results given in parameters
     * You have to return results
     *
     * @param array $results
     *
     * @return array
     */
    public function filter($results);
}
