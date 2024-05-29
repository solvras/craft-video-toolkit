<?php

namespace solvras\craftvideotoolkit\services;

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
 * @TODO add plugin settings for standard settings
 *
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
        if ($video instanceof Video && $video->getEmbedCode()) {
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
        } elseif ($this->isUrl($url)) {
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
