<?php
namespace DatingVIP\Component\Etcd\Http;

use DatingVIP\Component\Etcd\HttpInterface;
use DatingVIP\Component\Etcd\Exception\KeyNotFoundException;
use DatingVIP\Component\Etcd\Exception\KeyExistsException;
use DatingVIP\Component\Etcd\Exception\EtcdException;

class Curl implements HttpInterface
{
    private $is_https = false;

    private $verify_ssl_peer = true;

    private $custom_ca_file = '';

    public function verifySslPeer(bool $flag = true, string $ca_file = '') : void
    {
        $this->verify_ssl_peer = $flag;
        $this->custom_ca_file = $ca_file;
    }

    public function get(string $url, array $query_arguments = []) : array
    {
        if (!empty($query_arguments)) {
            $url .= '?' . http_build_query($query_arguments);
        }

        return $this->execute($this->curl($url), $url);
    }

    public function post(string $url, array $payload = [], array $query_arguments = []) : array
    {
        if (!empty($query_arguments)) {
            $url .= '?' . http_build_query($query_arguments);
        }

        $curl = $this->curl($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));

        return $this->execute($curl, $url);
    }

    public function put(string $url, array $payload = [], array $query_arguments = [])
    {
        if (!empty($query_arguments)) {
            $url .= '?' . http_build_query($query_arguments);
        }

        $curl = $this->curl($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));

        return $this->execute($curl, $url);
    }

    public function delete(string $url, array $query_arguments = [])
    {
        if (!empty($query_arguments)) {
            $url .= '?' . http_build_query($query_arguments);
        }
        $curl = $this->curl($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->execute($curl, $url);
    }

    private function curl(string $url)
    {
        if (!($curl = curl_init($url))) {
            throw new \RuntimeException("Can't create curl handle");
        }

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if ($this->is_https && $this->verify_ssl_peer) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            if ($this->custom_ca_file) {
                curl_setopt($curl, CURLOPT_CAINFO, $this->custom_ca_file);
            }
        } else {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        return $curl;
    }

    private function curlExec($curl, string $url)
    {
        $response = curl_exec($curl);
        if ($error_code = curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);

            throw new \RuntimeException("$url request failed. Reason: $error", $error_code);
        }
        curl_close($curl);

        return $response;
    }

    private function execute($curl, string $url, bool $decode_etcd_json = true)
    {
        $response = $this->curlExec($curl, $url);
        if ($decode_etcd_json) {
            $response = json_decode($response, true);

            if (isset($response['errorCode']) && $response['errorCode']) {
                $message = $response['message'];
                if (isset($response['cause']) && $response['cause']) {
                    $message .= '. Cause: ' . $response['cause'];
                }
                switch ($response['errorCode']) {
                    case 100:
                        throw new KeyNotFoundException($message);
                    case 105:
                        throw new KeyExistsException($message);
                    default:
                        throw new EtcdException($message);
                }
            }
        }

        return $response;
    }
}
