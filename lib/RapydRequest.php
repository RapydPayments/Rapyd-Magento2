<?php

namespace Rapyd\Rapydmagento2\lib;

use DateTime;

class RapydRequest
{
    private $access_key;
    private $secret_key;
    private $test_access_key;
    private $test_secret_key;
    private $test_mode;

    public function __construct($access_key, $secret_key, $test_mode, $test_access_key, $test_secret_key)
    {
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
        $this->test_access_key = $test_access_key;
        $this->test_secret_key = $test_secret_key;
        $this->test_mode = $test_mode;
    }

    public function generateRapydToken($body)
    {
        $response = $this->make_request_to_rapyd("post", \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_REDIRECT_PATH, $body);
        return $response;
    }

    public function make_request_to_rapyd($method, $path, $body = null)
    {
        $base_url = $this->rapyd_get_api_url();
        $access_key =  $this->rapyd_get_access_key();     // The access key received from Rapyd.
        $secret_key = $this->rapyd_get_secret_key(); // Never transmit the secret key by itself.

        $http_method = $method;                // Lower case.
        $salt = random_int(10000000, 99999999);// Randomly generated for each request.
        $date = new DateTime();
        $timestamp = $date->getTimestamp();  // Current Unix time.

        $body_string = !is_null($body) ? json_encode($body, JSON_UNESCAPED_SLASHES) : '';
        $sig_string = "$http_method$path$salt$timestamp$access_key$secret_key$body_string";

        $hash_sig_string = hash_hmac("sha256", $sig_string, $secret_key);
        $signature = base64_encode($hash_sig_string);

        $request_data = null;
        $test_mode = $this->rapyd_get_test_mode();

        if ($method === 'post') {
            $request_data = [
                CURLOPT_URL => "$base_url$path",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body_string

            ];
        } else {
            $request_data = [
                CURLOPT_URL => "$base_url$path",
                CURLOPT_RETURNTRANSFER => true,
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, $request_data);

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "access_key: $access_key",
            "salt: $salt",
            "timestamp: $timestamp",
            "signature: $signature",
            "test_mode: $test_mode"
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception("cURL Error #:" . $err);
        } else {
            return json_decode($response, true);
        }
    }

    public function rapyd_get_access_key()
    {
        if ('1' == $this->test_mode || 'yes' == $this->test_mode) {
            return $this->test_access_key;
        }
        return $this->access_key;
    }

    public function rapyd_get_secret_key()
    {
        if ('1' == $this->test_mode || 'yes' == $this->test_mode) {
            return $this->test_secret_key;
        }
        return $this->secret_key;
    }

    public function rapyd_get_test_mode()
    {
        if ('1' == $this->test_mode || 'yes' == $this->test_mode) {
            return 'true';
        }
        return 'false';
    }

    public function rapyd_get_api_url()
    {
        if ('1' == $this->test_mode || 'yes' == $this->test_mode) {
            return \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_PLUGIN_URL_TEST;
        }
        return \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_PLUGIN_URL_PROD;
    }

    public function rapyd_get_toolkit_url()
    {
        if ('1' == $this->test_mode || 'yes' == $this->test_mode) {
            return \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_TOOLKIT_JS_URL_TEST;
        }
        return \Rapyd\Rapydmagento2\lib\RapydConsts::RAPYD_TOOLKIT_JS_URL_PROD;
    }
}
