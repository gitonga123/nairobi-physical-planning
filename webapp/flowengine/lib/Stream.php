<?php

/**
 * This class allows one to issue a web request to a specified server.
 *
 * @version   1.2.0
 */
class Stream {
    private $stream;    // The context that will be used to fetch info from the server
    private $params;    // The parameters that will be used to make the request
    private $multipartBoundary;  // Used when uploading files

    public function __construct() {
        $this->multipartBoundary = '--------------------------'.microtime(true);
    }

    /**
     * Ping a given service to check if it is available
     * 
     * @param   host
     * @param   port
     * @param   timeout
     * @return  stdClass
     */
    public function ping($host, $port=80, $timeout=5) {
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        $success = false;

        if ( is_resource($fp) ) {
            $success = true;
            fclose($fp);
        }

        return (object)array(
            'success'      => $success,
            'errorCode'    => $errno,
            'errorMessage' => $errstr,
        );
    }

    /**
     * Make the request
     * 
     * Example
     * <code>
     * $stream->sendRequest( array (
     *   'url'     => '',
     *   'method'  => 'GET',  // GET, POST, PUT, DELETE,
     *   'cookie'  => $response->cookie,  // Pass the cookies that were  
     *                                    // returned from a previous request,
     *   'ssl'     => 'default',  // default: ensure SSL verification
     *                            // self: When working with self-signed
     *                            //       certificates
     *                            // none: Disable ssl verification
     *                            // array: An array with the parameters as 
     *                            //        defined in https://www.php.net/manual/en/context.ssl.php
     *   'contentType' => 'text', // text: for sending plain text content to 
     *                                     the server
     *                            // json: for sending json data 
     *                            // multipart: for file uploads
     *                            // default: url-encoded data
     *   'data'    => array (
     *     'text' => 'some text',
     *     'area' => 125.2,
     *     'file' => array ('name'=>'/path/to/file', 'type'=>'application/zip')
     *   ),
     *   'headers' => array (
     *     // Additional headers to send with the request
     *   )
     * ));
     * </code>
     * 
     * @return  stdClass
     */
    public function sendRequest($params) {
        // Initialise the session with the various parameters
        $this->initSession($params);

        // Create the stream context that will be used to retrieve data
        $this->createContextStream();

        // Get the response from the server
        return $this->parseResponse();
    }

    /**
     * Initialise the parameters that will be used in the request
     */
    private function initSession($params) {
        $this->params = array_replace_recursive(array(
            'url'         => '',
            'method'      => 'GET',
            'cookie'      => '',
            'ssl'         => 'default',
            'data'        => array(),
            'contentType' => 'default',
            'headers'     => array()
        ), $params);

        $this->params['url'] = preg_replace('/[^\w\/\.\-:]/S', '', $this->params['url']);

        $m = strtoupper($this->params['method']);
        $this->params['method'] = ($m=='POST' || $m=='GET' || $m=='PUT' || $m=='DELETE') ? $m: 'GET';

        if ( !is_array($this->params['data']) ) $this->params['data'] = array();
        if ( !is_array($this->params['headers']) ) $this->params['headers'] = array();

        // Set the content type to use
        if ( 'text' === $this->params['contentType'] )
            $this->params['headers']['Content-Type'] = 'text/html; charset=UTF-8';
        else if ( 'json' === $this->params['contentType'] )
            $this->params['headers']['Content-Type'] = 'application/json; charset=UTF-8';
        else if ( 'multipart' === $this->params['contentType'] )
            $this->params['headers']['Content-Type'] = 'multipart/form-data; boundary='.$this->multipartBoundary;
        else
            $this->params['headers']['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
    }

    /**
     * Configure the stream session with the parameters that have been supplied
     */
    private function createContextStream() {
        $headers = array();
        foreach ( $this->params['headers'] as $key=>$value ) $headers[] = "$key: $value";
        if ( is_string($this->params['cookie']) && !empty($this->params['cookie']) ) 
            $headers[] = "Cookie: ".$this->params['cookie'];

        if ( 'GET' === $this->params['method'] && count($this->params['data']) > 0 )
            $this->params['url'] .= "?" . http_build_query($this->params['data']);

        $ssl = $this->params['ssl'];
        if ( !is_array($ssl) ) {
            $ssl = array(
                'verify_peer'       => 'none' !== $ssl,
                'verify_peer_name'  => 'none' !== $ssl,
                'allow_self_signed' => 'self' === $ssl
            );
        }

        // The stream context
        $this->stream = fopen(
            $this->params['url'], 'r', false, 
            stream_context_create(array(
                'ssl'  => $ssl,
                'http' => [
                    'method'  => $this->params['method'],
                    'header'  => implode("\r\n", $headers),
                    'content' => $this->getContent(),

                    // Configured Params
                    'max_redirects' => 0,
                    'ignore_errors' => true,
                ]
            ))
        );
    }

    /**
     * Use the data that has been supplied to create the valid response
     * 
     * https://stackoverflow.com/a/4247082
     */
    private function getContent() {
        if ( count($this->params['data']) === 0 || 'GET' === $this->params['method'] ) return '';
        else if ( 'text' === $this->params['contentType'] ) return $this->params['data'];
        else if ( 'json' === $this->params['contentType'] ) return json_encode($this->params['data']);
        else if ( 'multipart' !== $this->params['contentType'] ) return http_build_query($this->params['data']);

        $content = "";
        foreach ( $this->params['data'] as $name=>$value ) {
            $content .= "--" . $this->multipartBoundary . "\r\n";

            if ( is_array($value) ) {
                if ( 'raw' === $value['type'] ) {
                    $content .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n".file_get_contents($value['name'])."\r\n";
                }
                else {
                    $content .= "Content-Disposition: form-data; name=\"$name\"; filename=\"".basename($value['name'])."\"\r\n";
                    $content .= "Content-Type: ".$value['type']."\r\n\r\n".file_get_contents($value['name'])."\r\n";
                }
            }
            else {
                $content .= "Content-Disposition: form-data; name=\"$name\"\r\n\r\n$value\r\n";
            }
        }

        return $content . "--" . $this->multipartBoundary . "--\r\n";
    }

    /**
     * Get the response from the server
     */
    private function parseResponse() {
        if ( !is_resource($this->stream) ) {
            return (object)array(
                'meta'    => null,
                'status'  => null,
                'cookie'  => null,
                'headers' => null,
                'content' => null
            );
        }

        $meta = stream_get_meta_data($this->stream);
        $cookie = array();
        $status = 200;
        $headers = array();
        $content = null;

        foreach ( $meta['wrapper_data'] as $header ) {
            if ( false !== strpos($header, "HTTP/1.") ) {
                $status = (int)preg_replace(
                    ['/(HTTP\/1\.1)/', '/(HTTP\/1\.0)/', '/[^\d]+/'], 
                    ['', '', ''], 
                    $header
                );
                continue;
            }

            $split = strpos($header, ":");
            if ( false === $split ) continue;

            $key = trim(substr($header, 0, $split));
            $value = trim(substr($header, $split+1));

            if ( 'Set-Cookie' === $key ) $cookie[] = $value;
            else $headers[$key] = $value;
        }

        $cookie = implode('  ', $cookie);
        if ( 302 !== $status ) {
            $content = stream_get_contents($this->stream);
            $json = json_decode($content, true);
            if ( null !== $json ) $content = $json;
        }

        unset($meta['wrapper_data']);
        fclose($this->stream);

        return (object)array(
            'meta'    => $meta,
            'status'  => $status,
            'cookie'  => $cookie,
            'headers' => $headers,
            'content' => $content
        );
    }
}