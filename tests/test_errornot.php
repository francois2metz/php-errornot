<?php

require_once 'simpletest/autorun.php';
require_once '../errornot.php';
require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';

class MyMockAdapter extends Http_Request2_Adapter_Mock
{
    protected $request;
    public function sendRequest(HTTP_Request2 $request)
    {
        $this->request = $request;
        return parent::sendRequest($request);
    }

    public function getRequest()
    {
        return $this->request;
    }
}

class TestErrorNot extends UnitTestCase
{
    protected function createMockRequest($response, $adapter_name = 'Http_Request2_Adapter_Mock')
    {
        $mock_network = new $adapter_name();
        $response_file = fopen(dirname(__FILE__).'/responses/'. $response, 'r');
        $this->assertTrue($response_file !== false, 'cannot open responses/'. $response);
        $mock_network->addResponse($response_file);
        fclose($response_file);
        return $mock_network;
    }

    public function testSendRequestOk()
    {
        $mock_network = $this->createMockRequest('test_ok.txt');
        $errornot = new ErrorNot('http://localhost:3000/', 'test');
        $errornot->setNetworkAdapter($mock_network);
        $this->assertTrue($errornot->notify('my message', 'raised_at'), 'should be ok');
    }

    public function testSendRequestError()
    {
        $mock_network = $this->createMockRequest('test_404.txt');
        $errornot = new ErrorNot('http://localhost:3000/', 'test');
        $errornot->setNetworkAdapter($mock_network);
        $this->assertFalse($errornot->notify('my message', 'raised_at'), 'should be not ok');
    }

    public function testJsonParam()
    {
        $mock_network = $this->createMockRequest('test_ok.txt', 'MyMockAdapter');
        $errornot = new ErrorNot('http://localhost:3000/', 'test-key');
        $errornot->setNetworkAdapter($mock_network);
        $this->assertTrue($errornot->notify('my message', 'raised_at'), 'should be ok');
        $this->assertEqual(array('api_key' => 'test-key',
                                 'version' => '0.1.0',
                                 'error' => array('message' => 'my message',
                                                  'raised_at' => 'raised_at',
                                                  'backtrace' => null,
                                                  'request' => null,
                                                  'environment' => null,
                                                  'data' => null)), json_decode($mock_network->getRequest()->getBody(), true));
    }

    public function testJsonParamWithExtraParam()
    {
        $mock_network = $this->createMockRequest('test_ok.txt', 'MyMockAdapter');
        $errornot = new ErrorNot('http://localhost:3000/', 'test-key');
        $errornot->setNetworkAdapter($mock_network);
        $this->assertTrue($errornot->notify('my message', 'raised_at', array('test'), array('url' => 'http://example.net/'), array('PATH_INFO' => '/'), array('mydata1', 'mydata2')), 'should be ok');
        $this->assertEqual(array('api_key' => 'test-key',
                                 'version' => '0.1.0',
                                 'error' => array('message' => 'my message',
                                                  'raised_at' => 'raised_at',
                                                  'backtrace' => array('test'),
                                                  'request' => array('url' => 'http://example.net/'),
                                                  'environment' => array('PATH_INFO' => '/'),
                                                  'data' => array('mydata1', 'mydata2'))), json_decode($mock_network->getRequest()->getBody(), true));
    }
}
