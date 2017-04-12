<?php

namespace ApiConnector;

abstract class ApiConnector
{

    protected $requestName;
    protected $response;
    protected $apiUrl;
    protected $parameters;
    
    private $sprookiInstance;
    private $locale;
    private $version;
    private $apiUser;

    public function __construct($sprookiInstance, $api_user_id, $locale = 'en_US', $version = 2.3)
    {
        $this->sprookiInstance = $sprookiInstance;
        $this->apiUser = $this->get_api_user($api_user_id);  
        $this->apiUrl = API_URL;
        
        $this->locale = $locale;
        $this->version = $version;           
    }

    public abstract function build_request_parameters();

    public function is_ok()
    {
        return $this->response->is_ok();
    }

    public function send_request()
    {
        $time = date('Y-m-d H:i:s');
        $headers = array('x-sprooki-time: ' . $time
            , 'x-sprooki-key: ' . $this->apiUser->publicKey);

        $parameters = $this->build_request_parameters();        
        $payload = $this->build_request_payload($time, $parameters);

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        $curlCommand = 'curl -XPOST ';
        foreach ($headers as $header)
        {
            $curlCommand.= '-H "' . $header . '" ';
        }

        $curlCommand .= $this->apiUrl . " -d '" . $payload . "'";
        error_log($curlCommand . $output);
        
        $jsonObj = array();
        
        if(curl_errno($ch) || empty($output))
        {
            $jsonObj = array('result' => 'NOK', 
                'error' => array(
                    'code' => curl_errno($ch) ? curl_errno($ch) : -1,
                    'message' => curl_errno($ch) ? curl_error($ch) : 'Unknown error occurred')
                );
        }
        else
        {
            try 
            {
                $jsonObj = json_decode($output, TRUE);
            }
            catch (Exception $ex) 
            {
                $jsonObj = array('result' => 'NOK', 
                    'error' => array(
                        'code' => -1,
                        'message' => 'Unknown error occurred while proccessing response')
                );
            }
        }
        
        curl_close($ch);
        
        return new ApiResponse($jsonObj);;
    }

    public function process()
    {
        $parameters = $this->build_request();

        $this->send_request($parameters);

        if ($this->response->is_ok())
        {
            return TRUE;
        }

        return $this->response->get_error_message();
    }

    private function build_request_payload($time, array $parameters = array())
    {
        $params = array(
            'request' => $this->requestName
            , 'deviceid' => isset($parameters['deviceid']) ? $parameters['deviceid'] : 'sprookimanager'
            , 'devicetype' => isset($parameters['devicetype']) ? $parameters['devicetype'] : 'web'
            , 'compressed' => isset($parameters['compressed']) ? $parameters['compressed'] : FALSE
            , 'version' => !empty($parameters['version']) ? $parameters['version'] : $this->version
            , 'sessid' => isset($parameters['sessionid']) ? $parameters['sessionid'] : NULL
            , 'auth' => $this->get_auth($time, $parameters['params'])
            , 'locale' => isset($parameters['locale']) ? $parameters['locale'] : $this->locale
            , 'params' => empty($parameters['params']) ? (object) array() : $parameters['params']
        );

        return json_encode($params);
    }

    private function get_auth($time, array $parameters = array())
    {
        if (empty($parameters))
        {
            return md5($this->apiUser->publicKey
                    . $this->apiUser->privateKey
                    . '{}'
                    . $time);
        }

        return md5($this->apiUser->publicKey
                . $this->apiUser->privateKey
                . json_encode($parameters)
                . $time);
    }
    
    private function get_api_user($api_user_id)
    {
        return \ApiUser::get($this->sprookiInstance, $api_user_id);
    }
}
