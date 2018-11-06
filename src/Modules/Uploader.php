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

    public function lazyUploadVideo($size = null, $md5 = null, $title = 'New video', $method = 'single')
    {
        $endpoint = '/videos/create';
        $data = [
            'title' => $title,
            'upload_method' => $method,
            'size' => $size,
            'md5' => $md5,
        ];

        $response = $this->service->getPrivateConnection()->get($endpoint, $data);

        $video = new Video($this->service, $response->media->key);
        $url = $this->linkToUrl($response->link, $method);

        return [ $video, $url ];
    }

    public function uploadVideo($file, $title = 'New video')
    {
        $md5 = md5_file($file);
        $size = filesize($file);

        [$video, $url] = $this->lazyUploadVideo($size, $md5, $title, 's3');

        $this->uploadTo($file, $url);

        return $video;
    }

    protected function linkToUrl($link, $method = null)
    {
        $query = http_build_query($link->query);
        $url = $link->protocol . '://' . $link->address . $link->path . '?' . $query;

        if($method === 'single') {
            $url .= '&api_format=json';
        }

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
