<?php

namespace HubertNNN\JwPlatform\Contracts;

/**
 * @property string $title
 * @property string $description
 * @property \DateTime $updated
 * @property \DateTime $published
 * @property int $duration in seconds
 * @property string $publicUrl
 * @property string $image
 * @property string $tinyThumbnail 40px
 * @property string $smallThumbnail 120px
 * @property string $mediumThumbnail 320px
 * @property string $largeThumbnail 480px
 * @property string $status
 * @property string[] $tags
 * @property string $error
 * @property string $md5
 * @property int $size
 */
interface Video
{
    public function save();
}
