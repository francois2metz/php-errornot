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

/**
 * @package Services_ErrorNot
 */
class Services_ErrorNot
{
    protected $url;

    protected $api_key;

    protected $version = '0.1.0';

    protected $adapter = null;

    private $previous_exception_handler = null;

    /**
     * Create a new notifier
     * @param String $url url of errornot instance
     * @param String $api_key api key of project
     * @param boolean $install_exception_handler
     */
    public function __construct($url, $api_key, $install_exception_handler = false)
    {
        $this->url     = $url;
        $this->api_key = $api_key;
        if ($install_exception_handler)
        {
            $this->installExceptionHandler();
        }
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
     * Notify Exception
     * @param Exception $exception
     */
    public function notifyException(Exception $exception)
    {
        $this->notify($exception->getMessage(),
                      null, // auto now
                      $exception->getTrace(),
                      array('params' => array('post' => $_POST, 'get' => $_GET, 'cookies' => $_COOKIE)),
                      $_SERVER,
                      isset($_SESSION) ? $_SESSION : '');
        if (!is_null($this->previous_exception_handler))
        {
            call_user_func($this->previous_exception_handler, $exception);
        }
    }

    /**
     * Notify a new error
     * @param String $message
     * @param Date $raised_at UTC date
     * @param array $backtrace
     * @param array $request
     * @param array $environnement
     * @param array $data
     * @return boolean
     */
    public function notify($message, $raised_at = null, $backtrace = array(), $request = null, $environnement = null, $data = null)
    {
        $http_request = new HTTP_Request2($this->formatUrl() , HTTP_Request2::METHOD_POST);
        if (!is_null($this->adapter))
        {
            $http_request->setAdapter($this->adapter);
        }
        if (is_null($raised_at))
        {
            $raised_at = date('c');
        }
        $http_request->addPostParameter('api_key', $this->api_key);
        $http_request->addPostParameter('version', $this->version);
        $http_request->addPostParameter('error', array('message'     => $message,
                                                       'raised_at'   => $raised_at,
                                                       'backtrace'   => $backtrace,
                                                       'request'     => $request,
                                                       'environment' => $environnement,
                                                       'data'        => $data));

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

    /**
     * Install exception handler
     * Handler not caught exceptions
     * Preserve previous exception handler
     */
    public function installExceptionHandler()
    {
        $this->previous_exception_handler = set_exception_handler(array($this, 'notifyException'));
    }

    protected function formatUrl()
    {
        return $this->url . (($this->url[strlen($this->url) - 1] == '/') ? '' : '/') . 'errors/';
    }
}
