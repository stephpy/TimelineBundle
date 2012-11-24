<?php

namespace Spy\TimelineBundle\Tests;

use Spy\TimelineBundle\Model\Collection;
use Spy\TimelineBundle\Model\TimelineAction;
use Spy\TimelineBundle\Spread\Deployer;

use Spy\TimelineBundle\Manager;

/**
 * ManagerTest
 *
 * @author Stephane PY <py.stephane1@gmail.com>
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testPush
     */
    public function testPushLater()
    {
        $manager  = $this->getMock('Spy\TimelineBundle\Entity\TimelineActionManager', array(), array(), '', false);
        $deployer = $this->getMock('Spy\TimelineBundle\Spread\Deployer', array(), array(), '', false);
        $ta       = $this->getMock('Spy\TimelineBundle\Model\TimelineAction');
        $provider = $this->getMock('Spy\TimelineBundle\Provider\Redis', array(), array(), '', false);

        $manager->expects($this->once())
            ->method('updateTimelineAction')
            ->with($this->equalTo($ta));

        $deployer->expects($this->once())
            ->method('getDelivery')
            ->will($this->returnValue(Deployer::DELIVERY_WAIT));

        $deployer->expects($this->never())
            ->method('deploy');

        $pusher = new Manager($manager, $deployer, $provider);
        $result = $pusher->push($ta);

        $this->assertFalse($result);
    }

    /**
     * testPushNow
     */
    public function testPushNow()
    {
        $manager  = $this->getMock('Spy\TimelineBundle\Entity\TimelineActionManager', array(), array(), '', false);
        $deployer = $this->getMock('Spy\TimelineBundle\Spread\Deployer', array(), array(), '', false);
        $ta       = $this->getMock('Spy\TimelineBundle\Model\TimelineAction');
        $provider = $this->getMock('Spy\TimelineBundle\Provider\Redis', array(), array(), '', false);

        $manager->expects($this->once())
            ->method('updateTimelineAction')
            ->with($this->equalTo($ta));

        $deployer->expects($this->once())
            ->method('getDelivery')
            ->will($this->returnValue(Deployer::DELIVERY_IMMEDIATE));

        $deployer->expects($this->once())
            ->method('deploy')
            ->with($this->equalTo($ta));

        $pusher = new Manager($manager, $deployer, $provider);
        $result = $pusher->push($ta);

        $this->assertTrue($result);
    }

    /**
     * testGetWall
     */
    public function testGetWall()
    {
        $manager  = $this->getMock('Spy\TimelineBundle\Entity\TimelineActionManager', array(), array(), '', false);
        $deployer = $this->getMock('Spy\TimelineBundle\Spread\Deployer', array(), array(), '', false);
        $provider = $this->getMock('Spy\TimelineBundle\Provider\Redis', array(), array(), '', false);

        $coll = array(
            $this->createTimelineAction(1),
        );

        $paramsExpected = array(
            'subjectModel' => 'toto',
            'subjectId' => 1,
            'context' => 'GLOBAL',
        );

        $optionsExpected = array();

        $provider->expects($this->once())
            ->method('getWall')
            ->with($this->equalTo($paramsExpected), $this->equalTo($optionsExpected))
            ->will($this->returnValue($coll));

        $manager = new Manager($manager, $deployer, $provider);

        $result = $manager->getWall('toto', 1);

        $this->assertEquals($result, new Collection($coll));
    }

    /**
     * testGetTimeline
     */
    public function testGetTimeline()
    {
        $manager  = $this->getMock('Spy\TimelineBundle\Entity\TimelineActionManager', array(), array(), '', false);
        $deployer = $this->getMock('Spy\TimelineBundle\Spread\Deployer', array(), array(), '', false);
        $provider = $this->getMock('Spy\TimelineBundle\Provider\Redis', array(), array(), '', false);

        $coll = array(
            $this->createTimelineAction(1),
        );

        $paramsExpected = array(
            'subjectModel' => 'toto',
            'subjectId' => 1,
        );

        $optionsExpected = array();

        $manager->expects($this->once())
            ->method('getTimeline')
            ->with($this->equalTo($paramsExpected), $this->equalTo($optionsExpected))
            ->will($this->returnValue($coll));

        $manager = new Manager($manager, $deployer, $provider);
        $result = $manager->getTimeline('toto', 1);

        $this->assertEquals($result, new Collection($coll));
    }

    /**
     * testApplyNoFilter
     */
    public function testApplyNoFilter()
    {
        $manager  = $this->getMock('Spy\TimelineBundle\Entity\TimelineActionManager', array(), array(), '', false);
        $deployer = $this->getMock('Spy\TimelineBundle\Spread\Deployer', array(), array(), '', false);
        $provider = $this->getMock('Spy\TimelineBundle\Provider\Redis', array(), array(), '', false);

        $manager = new Manager($manager, $deployer, $provider);
        $coll = array(
            $this->createTimelineAction(1),
        );

        $results = $manager->applyFilters($coll);

        $this->assertEquals($results, $coll);
    }

    /**
     * testApplyFilter
     */
    public function testApplyFilter()
    {
        $manager  = $this->getMock('Spy\TimelineBundle\Entity\TimelineActionManager', array(), array(), '', false);
        $deployer = $this->getMock('Spy\TimelineBundle\Spread\Deployer', array(), array(), '', false);
        $provider = $this->getMock('Spy\TimelineBundle\Provider\Redis', array(), array(), '', false);
        $filter   = $this->getMock('Spy\TimelineBundle\Tests\Fixtures\Filter');

        $coll = array(
            $this->createTimelineAction(1),
        );

        $filter->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($coll))
            ->will($this->returnValue(array(1)));

        $manager = new Manager($manager, $deployer, $provider);
        $manager->addFilter($filter);

        $results = $manager->applyFilters($coll);

        $this->assertEquals($results, array(1));
    }
    /**
     * @param int $id
     *
     * @return TimelineAction
     */
    private function createTimelineAction($id = 1)
    {
        $subject = $this->getMock('Spy\TimelineBundle\Tests\Fixtures\TimelineActionEntity');
        $subject ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        $cod = $this->getMock('Spy\TimelineBundle\Tests\Fixtures\TimelineActionEntity');
        $cod->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        $timeline = new TimelineAction();
        $timeline->create($subject, 'verb', $cod);

        return $timeline;
    }
}
