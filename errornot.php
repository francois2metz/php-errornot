<?php
/**
 * ErrorNot Notifier http://github.com/AF83/ErrorNot
 * Copyright (C) 2010  François de Metz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class ErrorNot
{
    protected $url;

    protected $api_key;

    protected $version;

    protected $adapter = null;
    /**
     * Create a new notifier
     * @param String $url url of errornot instance
     * @param String $api_key
     * @param String $version
     */
    public function __construct($url, $api_key, $version = '0.1.0')
    {
        $this->url     = $url;
        $this->api_key = $api_key;
        $this->version = $version;
    }
    /**
     * Set HTTP_Request2 Adapter
     * Useful for unit testing
     */
    public function setNetworkAdapter(HTTP_Request2_Adapter $http_request2_adapter)
    {
        $this->adapter = $http_request2_adapter;
    }
    /**
     * Notify a new error
     * @param String $message
     * @param Date $raised_at
     * @param array $backtrace
     * @param array $request
     * @param array $environnement
     * @param array $data
     * @return boolean
     */
    public function notify($message, $raised_at, $backtrace = null, $request = null, $environnement = null, $data = null)
    {
        $http_request = new HTTP_Request2($this->url . '/errors/', HTTP_Request2::METHOD_POST);
        if (!is_null($this->adapter))
        {
            $http_request->setAdapter($this->adapter);
        }
        $body = array('api_key' => $this->api_key,
                      'version' => $this->version,
                      'error'   => array('message'     => $message,
                                         'raised_at'   => $raised_at,
                                         'backtrace'   => $backtrace,
                                         'request'     => $request,
                                         'environment' => $environnement,
                                         'data'        => $data));
        $http_request->setBody(json_encode($body));
        try
        {
            $response = $http_request->send();
            if ($response->getStatus() == 200)
            {
                return true;
            }
            return false;
        }
        catch (HTTP_Request2_Exception $e)
        {
            return false;
        }
    }
}
