<?php

namespace solvras\craftvideotoolkit\web\twig;

use craft\elements\Asset;
use solvras\craftvideotoolkit\video\Video;
use solvras\craftvideotoolkit\VideoToolkit;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

/**
 * Twig extension
 */
class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('videoToolkit', [$this, 'videoToolkit' ], ['is_safe' => ['html']]),
        ];
    }

    public function videoToolkit(Asset|string $url, array $options = []): Video|string
    {
        $return = $options['return'] ?? 'video'; // video is default
        $video = VideoToolkit::getInstance()->videoToolkit->videoToolkit($url, $options);
        return match ($return) {
            'video' => new Markup($video, 'utf-8'),
            'videoUrl' => $video->getEmbedUrl(),default => false,
            'thumbnail' => new Markup($video->thumbnail(), 'utf-8'),
            'thumbnailUrl' => $video->getThumbnailUrl(),
        };
    }
}
