<?php

namespace HubertNNN\JwPlatform\Modules;

use HubertNNN\JwPlatform\JwPlatformService;
use HubertNNN\JwPlatform\Video;

class PlayerProvider
{
    /** @var JwPlatformService */
    protected $service;
    protected $players;

    public function __construct($service, $players)
    {
        $this->service = $service;
        $this->players = $players;
    }

    public function getPlayer($player = null)
    {
        if($player === null) {
            $player = 'default';
        }

        if(!isset($this->players[$player])) {
            return null;
        }

        $player = $this->players[$player];

        $resource = '/libraries/' . $player . '.js';
        return $this->service->getPublicConnection()->getUrl($resource, [], 24*3600, 12*3600);
    }
}
