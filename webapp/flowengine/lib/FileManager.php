<?php

/**
 * This class is used to upload an IFC file to the IFC file server that it may 
 * be rendered to a json file which can be viewed on the browser.
 * 
 * @version   1.0.0
 */
class FileManager {
    private $bag;      // The login credentials to the server
    private $session;  // The session manager
    private $stream;   // The item responsible for making the api requests
    private $error;    // The exception with the error that has occurred

    /**
     * The constructor initialises the various parameters to be used by the 
     * class.
     * 
     * @param   config
     * @param   session
     */
    public function __construct($config, $session) {
        $this->bag = $config;
        $this->stream = new Stream();
        $this->session = $session;
    }

    /**
     * Ping a given service to check if it is available
     * 
     * @return  stdClass
     */
    public function ping() {
        $url = parse_url($this->bag->api, PHP_URL_HOST);

        $port = parse_url($this->bag->api, PHP_URL_PORT);
        if ( null === $port ) $port = 80;

        return $this->stream->ping($url, $port, 10);
    }

    /**
     * Given a token, generate the array used to 
     * 
     * @param   token
     * @return  object
     */
    public function hashRequest($context=array()) {
        $context['stamp'] = time();
        $context['username'] = $this->bag->user;
        $context['token'] = hash('sha256', $this->bag->token.$context['stamp']);
        return $context;
    }

    /**
     * Check if one is still logged in using the parameters kept in the session
     * 
     * @return  stdClass
     */
    public function isLoggedIn() {
        if ( null === $this->session->ifcAPI || !isset($this->session->ifcAPI->cookie) ) 
            return (object)array('status'=>null);
        
        return $this->stream->sendRequest( array (
            'url'    => $this->bag->api.'/login/api',
            'cookie' => $this->session->ifcAPI->cookie
        ));
    }

    /**
     * Use this to log into the server
     * 
     * @return  boolean
     */
    public function logIn() {
        $response = $this->isLoggedIn();
        if ( 200 === $response->status && true === $response->content['authenticated'] ) 
            return $response;

        $echo = $this->stream->sendRequest( array (
            'url' => $this->bag->api.'/login/api'
        ));

        $param = $echo->headers['X-CSRF-PARAM'];
        $response = $this->stream->sendRequest( array (
            'url'    => $this->bag->api.'/login/api',
            'method' => 'POST',
            'cookie' => $echo->cookie,
            'data'   => $this->hashRequest( array (
                "$param"   => $echo->headers['X-CSRF-TOKEN']
            ))
        ));

        $ifcAPI = new \stdClass();
        $ifcAPI->loggedIn = false;

        if ( true === $response->content['authenticated'] ) {
            $ifcAPI->csrfParam = $echo->headers['X-CSRF-PARAM'];
            $ifcAPI->csrfToken = $echo->headers['X-CSRF-TOKEN'];
            $ifcAPI->cookie = $echo->cookie;
            $ifcAPI->loggedIn = true;
        }

        $this->session->ifcAPI = $ifcAPI;
        return $response;
    }

    /**
     * Use this to send the given file to the server
     *
     * The options returned are:
     * - denied: When the user supplied could not perform the specified task
     * - error: When an error occurs when processing the request. The error can 
     *          be obtained using the getLastError method
     * - ok: the task ended successfully
     * 
     * @param   appNumber
     * @param   filePath
     * @param   callback
     * @return  string
     */
    public function sendFile($appNumber, $filePath, $callback) {
        $this->error = null;
        $return = "ok";

        try {
            if ( false === $this->logIn()->content['authenticated'] ) {
                return "denied";
            }
            
            $param = $this->session->ifcAPI->csrfParam;
            $temp = $this->createArchive($filePath);

            $response = $this->stream->sendRequest( array (
                'url'       => $this->bag->api.'/viewer/api-upload',
                'method'    => 'POST',
                'contentType' => 'multipart',
                'cookie'    => $this->session->ifcAPI->cookie,
                'data'      => array (
                    "$param"   => $this->session->ifcAPI->csrfToken,
                    'file'     => array ('name'=>$temp, 'type'=>'application/zip'),
                    'app'      => $appNumber,
                    'callback' => $callback
                )
            ));

            unlink($temp);

            if ( 200 === $response->status ) return $response;

            $this->error = $response;
            $return = "error";
        }
        catch (\Exception $e) {
            $this->error = $e;
            $return = "error";
        }
        
        return $return;
    }

    /**
     * Use this section to get the geometry file to be rendered on the browser
     * 
     * @param   application
     * @return  stdClass
     */
    public function getGeometry($app) {
        $this->error = null;
        $return = "ok";

        try {
            if ( false === $this->logIn()->content['authenticated'] ) {
                return "denied";
            }

            $response = $this->stream->sendRequest( array (
                'url'       => $this->bag->api.'/viewer/code',
                'method'    => 'GET',
                'jsonParse' => false,
                'cookie'    => $this->session->ifcAPI->cookie,
                'data'      => array('q' => $app)
            ));

            if ( 200 === $response->status ) return $response;

            $this->error = $response;
            $return = "error";
        }
        catch (\Exception $e) {
            $this->error = $e;
            $return = "error";
        }
        
        return $return;
    }

    /**
     * The object with the exception that has occured due to the send file 
     * request
     * 
     * @return  stdClass
     */
    public function getLastError() { return $this->error; }

    /**
     * Create the temporary zip file that will be sent to the server for 
     * processing
     * 
     * @param   filePath
     * @return  string
     * @throws  Exception
     */
    private function createArchive($filePath) {
        $temp = tempnam(sys_get_temp_dir(), "ifc");

        $zip = new \ZipArchive();
        if ( $zip->open($temp, \ZipArchive::CREATE|\ZipArchive::OVERWRITE) !== true )
            throw new \Exception("The system's temp directory `".sys_get_temp_dir()."` could not be used!");

        $zip->addFile($filePath, basename($filePath));
        $zip->close();

        return $temp;
    }
}