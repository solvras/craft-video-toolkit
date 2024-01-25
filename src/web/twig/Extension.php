<?php

namespace solvras\craftvideotoolkit\web\twig;

use Craft;
use craft\elements\Asset;
use solvras\craftvideotoolkit\video\Video;
use solvras\craftvideotoolkit\VideoToolkit;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

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

    public function videoToolkit(string $url, array $options = []): Video|string
    {
        $return = $options['return'] ?? 'video';
        $video = VideoToolkit::getInstance()->videoToolkit->videoToolkit($url, $options);
        return match ($return) {
            'video' => new Markup($video, 'utf-8'),
            'thumbnail' => new Markup($video->thumbnail(), 'utf-8'),
            'thumbnailUrl' => $video->getThumbnailUrl(),
            'embedUrl' => $video->getEmbedUrl(),
            default => false,
        };
    }

}
