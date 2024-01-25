<?php

namespace solvras\craftvideotoolkit\services;

use Craft;
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
            case 'local':
                //echo 'local';
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


    // get youtube thumbnail url


    // get video thumbnail url
    public function getVideoThumbnailUrl($url): string
    {
        $video = null;
        switch ($this->getVideoKind($url)) {
            case 'youtube':
                $video = new Youtube($url);
                break;
            case 'vimeo':
                $video = new Vimeo($url);
                break;
            case 'local':
                //echo 'local';
                break;
            default:
                //echo 'no video';
                break;
        }
        if ($video instanceof Video) {
            return $video->getThumbnailUrl();
        }
        return '';
    }

    // get local video embed code
    // @TODO check other formats than mp4
    public function getLocalVideoEmbedCode($url, $options = []): string
    {
        $height = $options['height'] ?? self::HEIGHT;
        $width = $options['width'] ?? self::WIDTH;
        $responsive = $options['responsive'] ?? false;
        $muted = $options['muted'] ?? false;
        $autoplay = $options['autoplay'] ?? false;
        $loop = $options['loop'] ?? false;
        $controls = $options['controls'] ?? true;
        $attrArray = [];
        if ($muted) {
            $attrArray[] = 'muted';
        }
        if ($autoplay) {
            $attrArray[] = 'autoplay';
            $attrArray[] = 'muted';
        }
        if ($loop) {
            $attrArray[] = 'loop';
        }
        if ($controls) {
            $attrArray[] = 'controls';
        }

        if ($responsive) {
            $attrSize = "";
            $styleSize = "style='width:100%;height:100%;'";
        } else {
            $attrSize = "width='" . $width . "' height='" . $height . "'";
            $styleSize = "";
        }

        if ($url) {
            return '<video ' . implode(' ', $attrArray) . ' ' . $attrSize . ' ' . $styleSize . '><source src="' . $url . '" type="video/mp4"></video>';
        }
        return '';
    }

    // check if YouTube, vimeo video or local, return kind
    public function getVideoKind($url): string
    {
        if (str_contains($url, 'youtu')) {
            return 'youtube';
        } elseif (str_contains($url, 'vimeo')) {
            return 'vimeo';
        } elseif (!str_contains($url, 'http')) {
            return 'local';
        }
        return '';
    }
}