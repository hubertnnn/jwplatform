<?php

namespace HubertNNN\JwPlatform;

use Firebase\JWT\JWT;

class Connection
{
    protected $service;
    protected $apiKey;
    protected $tokenSecret;
    protected $baseUrl;
    protected $isPrivateApi;

    public function __construct($service, $apiKey, $tokenSecret, $baseUrl, $isPrivateApi)
    {
        $this->service = $service;
        $this->apiKey = $apiKey;
        $this->tokenSecret = $tokenSecret;
        $this->baseUrl = $baseUrl;
        $this->isPrivateApi = $isPrivateApi;
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function get($resource, $parameters, $validTime = 4*3600, $normalizationTime = 3600)
    {
        $url = $this->getUrl($resource, $parameters, $validTime, $normalizationTime);

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url);
        $response = \GuzzleHttp\json_decode($response->getBody());

        return $response;
    }

    public function getUrl($resource, $parameters, $validTime = 4*3600, $normalizationTime = 3600)
    {
        if(strpos($resource, '/v2/') === 0) {
            return $this->getV2Url($resource, $parameters, $validTime, $normalizationTime);
        } else {
            return $this->getV1Url($resource, $parameters, $validTime, $normalizationTime);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    protected function getV1Url($resource, $parameters, $validTime = 4*3600, $normalizationTime = 3600)
    {
        if($this->isPrivateApi) {
            return $this->getPrivateV1Url($resource, $parameters);
        } else {
            return $this->getPublicV1Url($resource, $validTime, $normalizationTime);
        }
    }

    protected function getV2Url($resource, $parameters, $validTime = 4*3600, $normalizationTime = 3600)
    {
        $exp = ceil((time() + $validTime)/$normalizationTime) * $normalizationTime;

        $tokenBody = array_merge([
            'resource' => $resource,
            'exp' => $exp,
            'format' => 'json',
        ], $parameters);

        $token = JWT::encode($tokenBody, $this->tokenSecret);
        $url = $this->baseUrl . $resource . '?token=' . $token;

        return $url;
    }

    protected function getPublicV1Url($resource, $validTime = 2*3600, $normalizationTime = 3600)
    {
        if($validTime === null) {
            $url = $this->baseUrl . $resource;
        } else {
            $exp = ceil((time() + $validTime)/$normalizationTime) * $normalizationTime;
            $sig = md5(ltrim($resource, '/') . ':' . $exp . ':' . $this->tokenSecret);

            $url = $this->baseUrl . $resource . '?exp=' . $exp . '&sig=' . $sig;
        }

        return $url;
    }

    protected function getPrivateV1Url($resource, $parameters)
    {
        $data = array_merge([
            'api_format' => 'json',
            'api_key' => $this->apiKey,
            'api_timestamp' => time(),
            'api_nonce' => rand(10000000, 99999999), // 8 digit random number
        ], $parameters);

        ksort($data);
        $data['api_signature'] = sha1(http_build_query($data, null, '&', PHP_QUERY_RFC3986) . $this->tokenSecret);

        $url = $this->baseUrl . $resource . '?' . http_build_query($data);

        return $url;
    }

}
