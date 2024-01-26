<?php

namespace solvras\craftvideotoolkit\services;

use Craft;
use craft\elements\Asset;
use solvras\craftvideotoolkit\video\AssetVideo;
use solvras\craftvideotoolkit\video\LocalPath;
use solvras\craftvideotoolkit\video\UrlVideo;
use solvras\craftvideotoolkit\video\Video;
use solvras\craftvideotoolkit\video\Vimeo;
use solvras\craftvideotoolkit\video\Youtube;
use yii\base\Component;

/**
 * Video Toolkit service
 * @TODO clean up code, move methods to represent the different kinds of videos and helper functions
 * @TODO refactor options to be a params in the class instead of a parameter in each method
 * @TODO better checks for video urls, maybe use oembed
 * @TODO if we use oembed, can we get more information to use in the embed code?
 * @TODO if we use oembed, can we easily add support for more video services?
 * @TODO add cache support if using oembed to avoid too many requests
 */
class VideoToolkit extends Component
{
    public function videoToolkit($url, $options = []): Video|string
    {
        $video = null;
        switch ($this->getVideoKind($url)) {
            case 'youtube':
                $video = new Youtube($url, $options);
                break;
            case 'vimeo':
                $video = new Vimeo($url, $options);
                break;
            case 'localAsset':
                $video = new AssetVideo($url, $options);
                break;
            case 'localUrl':
                $video = new UrlVideo($url, $options);
                break;
            case 'localPath':
                $video = new LocalPath($url, $options);
                break;
            default:
                //echo 'no video';
                break;
        }
        if ($video instanceof Video) {
            return $video;
        }


        return '';
    }

    // check if url is url or local file path, return bool
    public function isUrl($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    // check if YouTube, vimeo video or local, return kind
    public function getVideoKind(Asset|string $url): string
    {
        if ($url instanceof Asset) {
            return 'localAsset';
        }elseif ($this->isUrl($url)) {
            if (str_contains($url, 'youtu')) {
                return 'youtube';
            } elseif (str_contains($url, 'vimeo')) {
                return 'vimeo';
            } else {
                return 'localUrl';
            }
        } elseif (!str_contains($url, 'http')) {
            return 'localPath';
        }

        return '';
    }
}