<?php

namespace Highco\TimelineBundle\Timeline\Spread;

use Highco\TimelineBundle\Model\TimelineAction;
use Highco\TimelineBundle\Timeline\Spread\Entry\EntryCollection;
use Highco\TimelineBundle\Timeline\Spread\Entry\Entry;

/**
 * @package HighcoTimelineBundle
 * @version 1.0.0
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class Manager
{
    /**
     * @var \ArrayIterator
     */
    protected $spreads;

    /**
     * @var EntryCollection
     */
    protected $results;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->spreads = new \ArrayIterator();
        $this->options = $options;
        $this->results = new EntryCollection(isset($options['on_global_context']) ? $options['on_global_context'] : true);
    }

    /**
     * @param InterfaceSpread $spread
     */
    public function add(InterfaceSpread $spread)
    {
        $this->spreads[] = $spread;
    }

    /**
     * @param TimelineAction $timelineAction
     */
    public function process(TimelineAction $timelineAction)
    {
        // can be defined on config.yml
        if (isset($this->options['on_me']) && $this->options['on_me']) {
            $entry = new Entry();
            $entry->subject_model = $timelineAction->getSubjectModel();
            $entry->subject_id    = $timelineAction->getSubjectId();

            $this->results->set('GLOBAL', $entry);
        }

        foreach ($this->spreads as $spread) {
            if ($spread->supports($timelineAction)) {
                $spread->process($timelineAction, $this->results);
            }
        }
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Clears the results of manager.
     */
    public function clear()
    {
        $this->results = new EntryCollection();
    }
}
