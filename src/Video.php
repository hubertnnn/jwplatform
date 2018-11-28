<?php

namespace HubertNNN\JwPlatform;

use HubertNNN\JwPlatform\Contracts;
use HubertNNN\JwPlatform\Utils\DateTimeUtils;

/**
 * Class Video
 * @package HubertNNN\JwPlatform
 *
 * @property string $title
 * @property string $description
 * @property \DateTime $updated
 * @property \DateTime $published
 * @property int $duration in seconds
 * @property string $publicUrl
 * @property string $streamingUrl
 * @property string $fallbackUrl
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
class Video implements Contracts\Video
{
    /** @var JwPlatformService */
    protected $service;
    protected $id;

    protected $isPublicLoaded;
    protected $isPrivateLoaded;
    protected $isGeneratedLoaded;
    protected $forUpdate = [];

    protected $title;
    protected $description;

    protected $updated;
    protected $published;
    protected $duration;

    protected $publicUrl;
    protected $streamingUrl;
    protected $fallbackUrl;

    protected $image;
    protected $tinyThumbnail;
    protected $smallThumbnail;
    protected $mediumThumbnail;
    protected $largeThumbnail;

    protected $status;
    protected $tags;
    protected $error;
    protected $md5;
    protected $size;


    public function __construct($service, $id)
    {
        $this->service = $service;
        $this->id = $id;
    }

    public function loadPublicData($data = null)
    {
        if($this->isPublicLoaded) {
            return;
        }

        if($data === null) {
            $endpoint = '/v2/media/' . $this->id;
            $response = $this->service->getPublicConnection()->get($endpoint, [], 30, 10);
            $data = $response->playlist[0];
        }

        $this->title = $data->title;
        $this->description = $data->description;
        $this->published = DateTimeUtils::timestampToDate($data->pubdate);
        $this->duration = $data->duration;

        $this->isPublicLoaded = true;
    }

    public function loadPrivateData($data = null)
    {
        if($this->isPrivateLoaded) {
            return;
        }

        if($data === null) {
            $endpoint = '/videos/show';
            $response = $this->service->getPrivateConnection()->get($endpoint, ['video_key' => $this->id]);
            $data = $response->video;
        }

        $this->title = $data->title;
        $this->description = $data->description;
        $this->published = DateTimeUtils::timestampToDate($data->date);
        $this->duration = (int)floor($data->duration);

        $this->status = $data->status;
        $this->updated = DateTimeUtils::timestampToDate($data->updated);
        $this->tags = empty($data->tags) ? [] : explode(',', $data->tags);
        $this->error = $data->error;
        $this->md5 = $data->md5;
        $this->size = $data->size;

        $this->isPrivateLoaded = true;
    }

    public function loadGeneratedData()
    {
        $resource = '/thumbs/'. $this->id . '-40.jpg';
        $this->tinyThumbnail = $this->service->getPublicConnection()->getUrl($resource, [], null);

        $resource = '/thumbs/'. $this->id . '-120.jpg';
        $this->smallThumbnail = $this->service->getPublicConnection()->getUrl($resource, [], null);

        $resource = '/thumbs/'. $this->id . '-320.jpg';
        $this->mediumThumbnail = $this->service->getPublicConnection()->getUrl($resource, [], null);

        $resource = '/thumbs/'. $this->id . '-480.jpg';
        $this->largeThumbnail = $this->service->getPublicConnection()->getUrl($resource, [], null);

        $resource = '/thumbs/'. $this->id . '-720.jpg';
        $this->image = $this->service->getPublicConnection()->getUrl($resource, [], null);

        $endpoint = '/v2/media/' . $this->id;
        $this->publicUrl = $this->service->getPublicConnection()->getUrl($endpoint, [], 4*3600, 5*60);


        $endpoint = '/manifests/' . $this->id . '.m3u8';
        $this->streamingUrl = $this->service->getPublicConnection()->getUrl($endpoint, [], 24*3600, 12*3600);

        $fallbackTemplate = $this->service->getFallbackTemplate();
        if($fallbackTemplate !== null) {
            $endpoint = '/videos/' . $this->id . '-' . $fallbackTemplate . '.mp4';
            $this->fallbackUrl = $this->service->getPublicConnection()->getUrl($endpoint, [], 24*3600, 12*3600);
        }

        $this->isGeneratedLoaded = true;
    }


    public function __get($name)
    {
        if($name === 'id') {
            return $this->id;
        }

        $loaders = [
            'generated' => [
                'publicUrl',
                'streamingUrl',
                'fallbackUrl',

                'image',
                'tinyThumbnail',
                'smallThumbnail',
                'mediumThumbnail',
                'largeThumbnail',
            ],
            'public' => [
                'title',
                'description',
                'published',
                'duration',
            ],
            'private' => [
                'title',
                'description',
                'published',
                'duration',

                'status',
                'updated',
                'tags',
                'error',
                'md5',
                'size',
            ],
        ];

        foreach ($loaders as $loader => $variables) {
            if(in_array($name, $variables)) {
                $variable = 'is' . ucfirst($loader) . 'Loaded';
                if($this->$variable) {
                    return $this->$name;
                }
            }
        }

        foreach ($loaders as $loader => $variables) {
            if(in_array($name, $variables)) {
                $method = 'load' . ucfirst($loader) . 'Data';
                call_user_func([$this, $method]);
                return $this->$name;
            }
        }

        throw new \InvalidArgumentException('Property not found: ' . $name);
    }

    public function __set($name, $value)
    {
        $fields = [
            'title',
            'description',
            'published',
        ];

        if(!in_array($name, $fields)) {
            throw new \InvalidArgumentException('Property is not writable: ' . $name);
        }

        $this->$name = $value;
        $this->forUpdate[$name] = $value;
    }

    public function save()
    {
        $fields = [
            'title' => 'title',
            'tags' => 'tags',
            'description' => 'description',
            'published' => 'date',
            'link' => 'link',
        ];

        $endpoint = '/videos/update';
        $data = [
            'video_key' => $this->id,
        ];

        foreach ($this->forUpdate as $field => $value) {
            if(!isset($fields[$field]))
                continue;

            if($value instanceof \DateTime) {
                $value = DateTimeUtils::dateToTimestamp($value);
            }

            if(is_array($value)) {
                $value = implode(',', $value);
            }

            $data[$fields[$field]] = $value;
        }

        // Reset update fields
        $this->forUpdate = [];

        $this->service->getPrivateConnection()->get($endpoint, $data);
    }

    public function delete()
    {
        $endpoint = '/videos/delete';
        $data = [
            'video_key' => $this->id,
        ];

        $response = $this->service->getPrivateConnection()->get($endpoint, $data);

        return $response->status === 'ok';
    }

}
