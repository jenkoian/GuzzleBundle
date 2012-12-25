<?php

namespace Playbloom\Bundle\GuzzleBundle\DataCollector;

use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Http\Message\RequestInterface as GuzzleRequestInterface;
use Guzzle\Http\Message\Response as GuzzleResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * GuzzleDataCollector.
 *
 * @author Ludovic Fleury <ludo.flery@gmail.com>
 */
class GuzzleDataCollector extends DataCollector
{
    private $profiler;

    public function __construct(HistoryPlugin $profiler)
    {
        $this->data = array(
            'calls'    => array(),
            'error_count' => 0,
            'methods'     => array(),
            'total_time'  => 0,
        );

        $this->profiler = $profiler;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        foreach($this->profiler as $call) {
            $request = $call;
            $response = $request->getResponse();
            $this->collectRequestMethod();

            $this->data['calls'][] = array(
                'request' => $request,
                'requestContent' => null,
                'response' => $response,
                'responseContent' => $response->getBody(true),
                'time' => $this->collectResponseTime($response),
                'error' => $this->collectResponseError($response)
            );
        }
    }

    public function getCalls()
    {
        return $this->data['calls'];
    }

    public function countErrors()
    {
        return $this->data['error_count'];
    }

    public function getMethods()
    {
        return $this->data['methods'];
    }

    public function getTotalTime()
    {
        return $this->data['total_time'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'guzzle';
    }

    protected function collectRequestMethod(GuzzleRequestInterface $request)
    {
        if (!isset($this->data['methods'][$request->getMethod()])) {
            $this->data['methods'][$request->getMethod()] = 0;
        }

        $this->data['methods'][$request->getMethod()]++;

        return $request->getMethod();
    }

    protected function collectResponseTime(GuzzleResponse $response)
    {
        $this->data['total_time'] += $response->getInfo('total_time');

        return array(
            'total' => $response->getInfo('total_time'),
            'connection' => $response->getInfo('connect_time')
        );
    }

    protected function collectResponseError(GuzzleResponse $response)
    {
        if ($response->isError()) {
            $this->data['error_count']++;

            return true;
        }

        return false;
    }
}
