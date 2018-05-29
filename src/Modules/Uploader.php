<?php

namespace HubertNNN\JwPlatform\Modules;

use GuzzleHttp\HandlerStack;
use HubertNNN\JwPlatform\JwPlatformService;
use HubertNNN\JwPlatform\Video;

class Uploader
{
    /** @var JwPlatformService */
    protected $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function uploadVideo($file, $title)
    {
        $md5 = md5_file($file);
        $size = filesize($file);

        $endpoint = '/videos/create';
        $data = [
            'title' => 'New video',
            'upload_method' => 's3',
            'size' => $size,
            'md5' => $md5,
        ];

        $response = $this->service->getPrivateConnection()->get($endpoint, $data);
        $id = $response->media->key;

        $url = $this->linkToUrl($response->link);
        $this->uploadTo($file, $url);

        return new Video($this->service, $id);
    }

    protected function linkToUrl($link)
    {
        $query = http_build_query($link->query);
        $url = $link->protocol . '://' . $link->address . $link->path . '?' . $query;

        return $url;
    }

    protected function uploadTo($file, $url)
    {
        $source = fopen($file, 'r');
        $size = filesize($file);

        // Prevent guzzle from adding extra headers,
        // since they prevent correct key validation on the other side
        $stack = HandlerStack::create();
        $stack->remove('prepare_body');
        $stack->remove('cookies');

        $client = new \GuzzleHttp\Client();
        $client->put($url, [
            'body' => $source,
            'handler' => $stack,
            'headers' => [
                'Content-Length' => $size,
            ],
        ]);
    }
}
