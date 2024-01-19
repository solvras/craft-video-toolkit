<?php

namespace solvras\craftvideotoolkit\services;

use Craft;
use yii\base\Component;

/**
 * Video Toolkit service
 */
class VideoToolkit extends Component
{
    // get id from youtube video
    public function getYoutubeId($url): string
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            if (isset($query['v'])) {
                return $query['v'];
            }
        }
        return '';
    }

    public function getVimeoId($url): string
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['path'])) {
            $path = explode('/', $parsedUrl['path']);
            if (isset($path[1])) {
                return $path[1];
            }
        }
        return '';
    }

    // get youtube embed url
    public function getYoutubeEmbedUrl($url): string
    {
        $id = $this->getYoutubeId($url);
        if ($id) {
            return 'https://www.youtube.com/embed/' . $id;
        }
        return '';
    }

    //get vimeo embed url, both from public and private urls
    public function getVimeoEmbedUrl($url): string
    {
        $id = $this->getVimeoId($url);
        if ($id) {
            return 'https://player.vimeo.com/video/' . $id;
        }
        return '';
    }

    // get youtube thumbnail url
    public function getYoutubeThumbnailUrl($url): string
    {
        $id = $this->getYoutubeId($url);
        if ($id) {
            return 'https://img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
        }
        return '';
    }

    // get vimeo thumbnail url
    public function getVimeoThumbnailUrl($url): string
    {
        $id = $this->getVimeoId($url);
        if ($id) {
            $hash = unserialize(file_get_contents('https://vimeo.com/api/v2/video/' . $id . '.php'));
            return $hash[0]['thumbnail_large'];
        }
        return '';
    }

    // get youtube embed code
    public function getYoutubeEmbedCode($url): string
    {
        $id = $this->getYoutubeId($url);
        if ($id) {
            return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
        return '';
    }

    // get vimeo embed code
    public function getVimeoEmbedCode($url): string
    {
        $id = $this->getVimeoId($url);
        if ($id) {
            return '<iframe src="https://player.vimeo.com/video/' . $id . '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
        }
        return '';
    }
}
