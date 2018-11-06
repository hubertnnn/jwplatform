<?php

namespace HubertNNN\JwPlatform\Contracts;

interface JwPlatformService
{
    /**
     * @param string $videoId
     * @return Video
     */
    public function getVideo($videoId);

    /**
     * @return Video[]
     */
    public function getVideos();

    /**
     * @param string $file
     * @param string $title
     * @return Video
     */
    public function createVideo($file, $title);

    /**
     * @param int $size
     * @param string $md5
     * @param string $title
     * @return Video
     */
    public function lazyCreateVideo($size, $md5, $title);

}
