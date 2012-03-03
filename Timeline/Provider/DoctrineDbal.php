<?php

namespace Highco\TimelineBundle\Timeline\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Highco\TimelineBundle\Model\TimelineAction;

/**
 * @uses InterfaceProvider
 * @uses InterfaceEntityRetriever
 * @package HighcoTimelineBundle
 * @version 1.0.0
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class DoctrineDbal implements InterfaceProvider, InterfaceEntityRetriever
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var InterfaceEntityRetriever
     */
    private $entityRetriever;

    /**
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getWall(array $params, $options = array())
    {
        throw new \OutOfRangeException("This method is not available yet for DoctrineDbal");
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeline(array $params, $options = array())
    {
        if (!isset($params['subject_model']) || !isset($params['subject_id'])) {
            throw new \InvalidArgumentException('You have to define a "subject_model" and a "subject_id" to pull data');
        }

        $offset = isset($options['offset']) ? $options['offset'] : 0;
        $limit  = isset($options['limit']) ? $options['limit'] : 10;
        $status = isset($options['status']) ? $options['status'] : 'published';

        $qb = $this->em->getRepository('HighcoTimelineBundle:TimelineAction')->createQueryBuilder('ta');

        $qb
            ->where('ta.subject_model = :subject_model')
            ->andWhere('ta.subject_id = :subject_id')
            ->andWhere('ta.status_current = :status')
            ->orderBy('ta.created_at', 'DESC')
            ->setParameter('subject_model', $params['subject_model'])
            ->setParameter('subject_id', $params['subject_id'])
            ->setParameter('status', $status)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(TimelineAction $timelineAction, $context, $subjectModel, $subjectId)
    {
        throw new \OutOfRangeException("This method is not available yet for DoctrineDbal");
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityRetriever(InterfaceEntityRetriever $entityRetriever = null)
    {
        $this->entityRetriever = $entityRetriever;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $qb = $this->em->getRepository('HighcoTimelineBundle:TimelineAction')->createQueryBuilder('ta');

        $qb
            ->add('where', $qb->expr()->in('ta.id', '?1'))
            ->orderBy('ta.created_at', 'DESC')
            ->setParameter(1, $ids)
        ;

        return $qb->getQuery()->getResult();
    }
}
