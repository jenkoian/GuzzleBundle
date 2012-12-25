<?php

namespace Playbloom\Bundle\GuzzleBundle\Tests\DataCollector;

use Playbloom\Bundle\GuzzleBundle\DataCollector\GuzzleDataCollector;
use Guzzle\Plugin\History\HistoryPlugin;


class GuzzleDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $guzzleDataCollector = $this->createGuzzleCollector();

        $this->assertEquals($guzzleDataCollector->getName(), 'guzzle');
    }

    public function testCollect()
    {
        // test an empty collector
        $guzzleDataCollector = $this->createGuzzleCollector();

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $guzzleDataCollector->collect($request, $response);

        $this->assertEquals($guzzleDataCollector->getCalls(), array());
        $this->assertEquals($guzzleDataCollector->countErrors(), 0);
        $this->assertEquals($guzzleDataCollector->getMethods(), array());
        $this->assertEquals($guzzleDataCollector->getTotalTime(), 0);

        // test a stubbed collector
        $guzzleDataCollector = $this->createGuzzleCollector(true);

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $guzzleDataCollector->collect($request, $response);

        $this->assertEquals(count($guzzleDataCollector->getCalls()), 1);
        $this->assertEquals($guzzleDataCollector->countErrors(), 0);
        $this->assertEquals($guzzleDataCollector->getMethods(), array('get' => 1));
        $this->assertEquals($guzzleDataCollector->getTotalTime(), 200);
    }

    protected function createGuzzleCollector($stubPlugin = false)
    {
        if ($stubPlugin) {
            $response = $this->getMock('Guzzle\Http\Message\Response', array(), array(200));
            $response->expects($this->any())
                ->method('getInfo')
                ->will($this->returnValue(200));

            $response->expects($this->any())
                ->method('getBody')
                ->with($this->equalTo(true))
                ->will($this->returnValue(''));

            $response->expects($this->any())
                ->method('isError')
                ->will($this->returnValue(false));


            $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
            $request->expects($this->any())
                ->method('getResponse')
                ->will($this->returnValue($response));

            $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('get'));

            $historyPlugin = new HistoryPluginStub(array($request));
        } else {
            $historyPlugin = new HistoryPluginStub(array());
        }

        return new GuzzleDataCollector($historyPlugin);
    }
}

class HistoryPluginStub extends HistoryPlugin implements \IteratorAggregate
{
    private $stubJournal = array();

    public function __construct(array $stubJournal)
    {
        $this->stubJournal = $stubJournal;
    }

    /**
     * Get the requests in the history
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->stubJournal);
    }
}
