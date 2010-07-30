<?php
class ErrorNotSocketNonBlockingHttpAdapter extends HTTP_Request2_Adapter_Socket
{
   /**
    * Sends request to the remote server without waiting for its response.
    *
    * @param    HTTP_Request2
    * @return   HTTP_Request2_Response
    * @throws   HTTP_Request2_Exception
    */
    public function sendRequest(HTTP_Request2 $request)
    {
        $this->request = $request;

        // Use global request timeout if given, see feature requests #5735, #8964
        if ($timeout = $request->getConfig('timeout')) {
            $this->deadline = time() + $timeout;
        } else {
            $this->deadline = null;
        }

        try {
            $keepAlive = $this->connect();
            $headers   = $this->prepareHeaders();
            if (false === fwrite($this->socket, $headers, strlen($headers))) {
                throw new HTTP_Request2_Exception('Error writing request');
            }
            // provide request headers to the observer, see request #7633
            $this->request->setLastEvent('sentHeaders', $headers);
            $this->writeBody();

            if ($this->deadline && time() > $this->deadline) {
                throw new HTTP_Request2_Exception(
                    'Request timed out after ' .
                    $request->getConfig('timeout') . ' second(s)'
                );
            }
            $this->disconnect();

        } catch (Exception $e) {
            $this->disconnect();
        }

        unset($this->request, $this->requestBody);

        if (!empty($e)) {
            throw $e;
        }
    }
}