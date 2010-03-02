<?php

require_once 'HTTP/Request2/Adapter/Mock.php';

class MyMockAdapter extends Http_Request2_Adapter_Mock
{
    protected $request;

    protected $nb_request = 0;

    public function sendRequest(HTTP_Request2 $request)
    {
        $this->request = $request;
        $this->nb_request++;
        return parent::sendRequest($request);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getNbRequest()
    {
        return $this->nb_request;
    }
}

function createMockRequest($response, $adapter_name = 'Http_Request2_Adapter_Mock')
{
    $mock_network = new $adapter_name();
    $response_file = fopen(dirname(__FILE__).'/responses/'. $response, 'r');
    $mock_network->addResponse($response_file);
    fclose($response_file);
    return $mock_network;
}
