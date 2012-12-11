<?php

namespace Spy\TimelineBundle\Driver\Redis;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Predis\Client as PredisClient;
use Snc\RedisBundle\Client\Phpredis\Client as PhpredisClient;
use Spy\Timeline\Driver\TimelineManagerInterface;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Model\ComponentInterface;
use Spy\Timeline\Model\TimelineInterface;
use Spy\Timeline\Pager\PagerInterface;

/**
 * TimelineManager
 *
 * @uses TimelineManagerInterface
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class TimelineManager implements TimelineManagerInterface
{
    /**
     * @var PredisClient|PhpredisClient
     */
    protected $client;

    /**
     * @var PagerInterface
     */
    protected $pager;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var boolean
     */
    protected $pipeline;

    /**
     * @var array
     */
    protected $persistedDatas = array();

    /**
     * @param PredisClient|PhpredisClient $client        client
     * @param PagerInterface              $pager         pager
     * @param string                      $prefix        prefix
     * @param boolean                     $pipeline      pipeline
     */
    public function __construct($client, PagerInterface $pager, $prefix, $pipeline = true)
    {
        if (!$client instanceof PredisClient && !$client instanceof PhpredisClient) {
            throw new \InvalidArgumentException('You have to give a PhpRedisClient or a PredisClient');
        }

        $this->client        = $client;
        $this->pager         = $pager;
        $this->prefix        = $prefix;
        $this->pipeline      = $pipeline;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeline(ComponentInterface $subject, array $options = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'page'         => 1,
            'max_per_page' => 10,
            'type'    => TimelineInterface::TYPE_TIMELINE,
            'context' => 'GLOBAL',
            'filter'  => true,
        ));

        $options = $resolver->resolve($options);

        $token   = new Pager\PagerToken($this->getRedisKey($subject, $options['context'], $options['type']));
        $pager   = $this->pager->paginate($token, $options['page'], $options['max_per_page']);

        if ($options['filter']) {
            return $this->pager->filter($pager);
        }

        return $pager;
    }

    /**
     * {@inheritdoc}
     */
    public function countKeys(ComponentInterface $subject, array $options = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'type'    => TimelineInterface::TYPE_TIMELINE,
            'context' => 'GLOBAL',
        ));

        $options = $resolver->resolve($options);

        $redisKey = $this->getRedisKey($subject, $options['context'], $options['type']);

        return $this->client->zCard($redisKey);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ComponentInterface $subject, $actionId, array $options = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'type'    => TimelineInterface::TYPE_TIMELINE,
            'context' => 'GLOBAL',
        ));

        $options = $resolver->resolve($options);

        $redisKey = $this->getRedisKey($subject, $options['context'], $options['type']);

        $this->persistedDatas[] = array(
            'zRem',
            $redisKey,
            $actionId,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(ComponentInterface $subject, array $options = array())
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'type'    => TimelineInterface::TYPE_TIMELINE,
            'context' => 'GLOBAL',
        ));

        $options = $resolver->resolve($options);

        $redisKey = $this->getRedisKey($subject, $options['context'], $options['type']);

        $this->persistedDatas[] = array(
            'del',
            $redisKey,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAndPersist(ActionInterface $action, ComponentInterface $subject, $context = 'GLOBAL', $type = TimelineInterface::TYPE_TIMELINE)
    {
        $redisKey = $this->getRedisKey($subject, $context, $type);

        $this->persistedDatas[] = array(
            'zAdd',
            $redisKey,
            $action->getSpreadTime(),
            $action->getId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (empty($this->persistedDatas)) {
            return array();
        }

        $client  = $this->client;
        $replies = array();

        if ($this->pipeline) {
            $client = $client->pipeline();
        }

        foreach ($this->persistedDatas as $persistData) {
            switch ($persistData[0]) {
                case 'del':
                    $replies[] = $client->del($persistData[1]);
                    break;
                case 'zAdd':
                    $replies[] = $client->zAdd($persistData[1], $persistData[2], $persistData[3]);
                    break;
                case 'zRem':
                    $replies[] = $client->zRem($persistData[1], $persistData[2]);
                    break;
                default:
                    throw new \OutOfRangeException('This function is not supported');
                    break;
            }
        }

        if ($this->pipeline) {
            if ($client instanceof \Predis\Pipeline\PipelineContext) {
                return $client->execute();
            } else {
                return $client->exec();
            }
        }

        return $replies;
    }

    /**
     * @param ComponentInterface $subject subject
     * @param string             $type    type
     * @param string             $context context
     *
     * @return string
     */
    protected function getRedisKey(ComponentInterface $subject, $type, $context)
    {
        return sprintf('%s:%s:%s:%s', $this->prefix, $subject->getHash(), $type, $context);
    }
}
