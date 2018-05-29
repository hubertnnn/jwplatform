<?php

namespace HubertNNN\JwPlatform\Modules;

use HubertNNN\JwPlatform\JwPlatformService;
use HubertNNN\JwPlatform\Video;

class PrivateFetcher
{
    /** @var JwPlatformService */
    protected $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function fetchVideos($page = 0, $pageSize = 100)
    {
        $endpoint = '/videos/list';
        $data = [
            'result_limit' => $pageSize,
            'result_offset' => $page,
        ];

        $response = $this->service->getPrivateConnection()->get($endpoint, $data);

        $videos = [];
        foreach ($response->videos as $videoData) {
            $video = new Video($this->service, $videoData->key);
            $video->loadPrivateData($videoData);
            $videos[] = $video;
        }

        return $videos;
    }
}
