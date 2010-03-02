<?php

require_once 'simpletest/autorun.php';
require_once 'HTTP/Request2.php';
require_once dirname(__FILE__).'/../errornot.php';
require_once 'mock.php';

class TestErrorNot extends UnitTestCase
{
    protected function createMockRequest($response, $adapter_name = 'Http_Request2_Adapter_Mock')
    {
        return createMockRequest($response, $adapter_name);
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
