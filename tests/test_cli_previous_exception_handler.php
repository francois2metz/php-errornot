<?php
require_once 'HTTP/Request2.php';
require_once dirname(__FILE__).'/../errornot.php';
require_once 'mock.php';

function myExceptionHandler($e)
{
    if ($e->getMessage() == 'test')
        exit(3);
    exit(4);
}

set_exception_handler('myExceptionHandler');

class MockAdapterWithNotify extends Http_Request2_Adapter_Mock
{
    public function sendRequest(HTTP_Request2 $request)
    {
        return $this->createResponseFromString("HTTP/1.1 200 OK\nServer: Apache\nOK\n");
    }
}

$mock_network = createMockRequest('test_ok.txt', 'MockAdapterWithNotify');
$errornot = new ErrorNot('http://localhost:3000/', 'test-key', true);
$errornot->setNetworkAdapter($mock_network);

throw new Exception('test');

exit(0);
