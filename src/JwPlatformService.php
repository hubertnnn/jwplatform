<?php

namespace HubertNNN\JwPlatform;

use HubertNNN\JwPlatform\Modules\PlayerProvider;
use HubertNNN\JwPlatform\Modules\PrivateFetcher;
use HubertNNN\JwPlatform\Modules\Uploader;

class JwPlatformService implements Contracts\JwPlatformService
{
    protected $apiKey;
    protected $tokenSecret;
    protected $players;
    protected $fallbackTemplate;

    public function __construct($apiKey, $tokenSecret, $players, $fallbackTemplate = null)
    {
        $this->apiKey = $apiKey;
        $this->tokenSecret = $tokenSecret;
        $this->players = $players;
        $this->fallbackTemplate = $fallbackTemplate;
    }

    public function getPublicConnection()
    {
        $baseUrl = 'https://cdn.jwplayer.com';

        return new Connection($this, $this->apiKey, $this->tokenSecret, $baseUrl, false);
    }

    public function getPrivateConnection()
    {
        $baseUrl = 'https://api.jwplatform.com';

        return new Connection($this, $this->apiKey, $this->tokenSecret, $baseUrl, true);
    }

    public function getFallbackTemplate()
    {
        return $this->fallbackTemplate;
    }

    public function getPlayerUrl($player = null)
    {
        return (new PlayerProvider($this, $this->players))->getPlayer($player);
    }

    public function getUploader()
    {
        return new Uploader($this);
    }

    public function getVideo($videoId)
    {
        return new Video($this, $videoId);
    }

    public function getVideos()
    {
        $fetcher = new PrivateFetcher($this);
        return $fetcher->fetchVideos();
    }

    public function createVideo($file, $title)
    {
        $uploader = new Uploader($this);
        return $uploader->uploadVideo($file, $title);
    }

}
