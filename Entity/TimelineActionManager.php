<?php

namespace Highco\TimelineBundle\Entity;

use Highco\TimelineBundle\Model\TimelineActionManagerInterface;
use Highco\TimelineBundle\Model\TimelineActionInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * TimelineActionManager
 *
 * @uses TimelineActionManagerInterface
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class TimelineActionManager implements TimelineActionManagerInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $timelineActionClass;

    /**
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em, $timelineActionClass)
    {
        $this->em = $em;
        $this->timelineActionClass = $timelineActionClass;
    }

    /**
     * Return actual entity manager
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimelineAction(TimelineActionInterface $timelineAction)
    {
        $this->em->persist($timelineAction);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimelineWithStatusPublished($limit = 10)
    {
        return $this->em
            ->getRepository($this->timelineActionClass)
            ->createQueryBuilder('ta')
            ->where('ta.statusWanted = :statusWanted')
            ->setMaxResults($limit)
            ->setParameter('statusWanted', TimelineAction::STATUS_PUBLISHED)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     * @param array $ids
     */
    public function getTimelineActionsForIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $qb = $this->em->getRepository($this->timelineActionClass)->createQueryBuilder('ta');

        $qb
            ->add('where', $qb->expr()->in('ta.id', '?1'))
            ->orderBy('ta.createdAt', 'DESC')
            ->setParameter(1, $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     * @param array $options
     */
    public function getTimeline(array $params, array $options = array())
    {
        if (!isset($params['subjectModel']) || !isset($params['subjectId'])) {
            throw new \InvalidArgumentException('You have to define a "subjectModel" and a "subjectId" to pull data');
        }

        $offset = isset($options['offset']) ? $options['offset'] : 0;
        $limit  = isset($options['limit']) ? $options['limit'] : 10;
        $status = isset($options['status']) ? $options['status'] : 'published';

        $qb = $this->em->getRepository($this->timelineActionClass)->createQueryBuilder('ta');

        $qb
            ->where('ta.subjectModel = :subjectModel')
            ->andWhere('ta.subjectId = :subjectId')
            ->andWhere('ta.statusCurrent = :status')
            ->orderBy('ta.createdAt', 'DESC')
            ->setParameter('subjectModel', $params['subjectModel'])
            ->setParameter('subjectId', $params['subjectId'])
            ->setParameter('status', $status)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
