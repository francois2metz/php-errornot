<?php

require_once 'simpletest/autorun.php';
require_once 'HTTP/Request2.php';
require_once dirname(__FILE__).'/../errornot.php';
require_once 'mock.php';

class TestErrorNot extends UnitTestCase
{
    protected function createMockRequest($url = 'http://localhost:3000/', $response_file = 'test_ok.txt', $adapter_name = 'MyMockAdapter')
    {
        $errornot = new ErrorNot($url, 'test-key');
        $mock_network = createMockRequest($response_file, $adapter_name);
        $errornot->setNetworkAdapter($mock_network);
        return array($errornot, $mock_network);
    }

    public function testSendRequestOk()
    {
        list($errornot, $mock_network) = $this->createMockRequest();
        $this->assertTrue($errornot->notify('my message', 'raised_at'), 'should be ok');
        $this->assertEqual($mock_network->getRequest()->getUrl()->getUrl(), 'http://localhost:3000/errors/');
        $this->assertEqual($mock_network->getRequest()->getMethod(), 'POST');
    }

    public function testSendRequestUrlWithoutEndSlash()
    {
        list($errornot, $mock_network) = $this->createMockRequest('http://localhost:3000');
        $this->assertTrue($errornot->notify('my message', 'raised_at'), 'should be ok');
        $this->assertEqual($mock_network->getRequest()->getUrl()->getUrl(), 'http://localhost:3000/errors/');
    }

    public function testSendRequestError()
    {
        list($errornot, $mock_network) = $this->createMockRequest('http://localhost:3000/', 'test_404.txt');
        $this->assertFalse($errornot->notify('my message', 'raised_at'), 'should be not ok');
    }

    public function testPostParams()
    {
        list($errornot, $mock_network) = $this->createMockRequest();
        $this->assertTrue($errornot->notify('my message', 'raised_at'), 'should be ok');
        $this->assertEqual('api_key=test-key&version=0.1.0&error[message]=my message&error[raised_at]=raised_at', urldecode($mock_network->getRequest()->getBody()));
    }

    public function testSetAutoNow()
    {
        list($errornot, $mock_network) = $this->createMockRequest();
        $this->assertTrue($errornot->notify('my message'), 'should be ok');
        $this->assertEqual('api_key=test-key&version=0.1.0&error[message]=my message&error[raised_at]='.date('c'), urldecode($mock_network->getRequest()->getBody()));
    }

    public function testPostExtraParams()
    {
        list($errornot, $mock_network) = $this->createMockRequest();
        $this->assertTrue($errornot->notify('my message', 'raised_at', array('test'), array('url' => 'http://example.net/'), array('PATH_INFO' => '/'), array('mydata1', 'mydata2')), 'should be ok');
        $this->assertEqual('api_key=test-key&version=0.1.0&error[message]=my message&error[raised_at]=raised_at&'.
                           'error[backtrace][0]=test&error[request][url]=http://example.net/&error[environment][PATH_INFO]=/'.
                           '&error[data][0]=mydata1&error[data][1]=mydata2', urldecode($mock_network->getRequest()->getBody()));
    }
}

class TestErrorNotExceptionHandler extends UnitTestCase
{
    public function testInstallExceptionHandler()
    {
        $cmd = '/usr/bin/php '. dirname(__FILE__) .'/test_cli_simple_exception_handler.php';
        $return_value = -1;
        passthru($cmd, $return_value);
        $this->assertEqual(2, $return_value);
    }

    public function testExceptionHandlerDontOverridePreviousOne()
    {
        $cmd = '/usr/bin/php '. dirname(__FILE__) .'/test_cli_previous_exception_handler.php';
        $return_value = -1;
        passthru($cmd, $return_value);
        $this->assertEqual(3, $return_value);
    }
}
