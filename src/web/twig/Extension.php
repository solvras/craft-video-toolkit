<?php

namespace solvras\craftvideotoolkit\web\twig;

use Craft;
use craft\elements\Asset;
use spicyweb\embeddedassets\models\EmbeddedAsset;
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
            new TwigFunction('videoEmbedUrl', [$this, 'getEmbedUrl' ]),
            new TwigFunction('videoThumbnailUrl', [$this, 'getVideoThumbnailUrl' ]),
            new TwigFunction('videoEmbedCode', [$this, 'getVideoEmbedCode' ], ['is_safe' => ['html']]),
            new TwigFunction('videoEmbedCodeResponsive', [$this, 'getVideoEmbedCodeResponsive' ], ['is_safe' => ['html']]),
        ];
    }

    public function videoToolkit(string $url, array $options = []): string
    {
        return new Markup(VideoToolkit::getInstance()->videoToolkit->videoToolkit($url, $options), 'utf-8');
    }

    public function getEmbedUrl(string $url): string
    {
        return VideoToolkit::getInstance()->videoToolkit->getEmbedUrl($url);
    }

    public function getVideoThumbnailUrl(string $url): string
    {
        return VideoToolkit::getInstance()->videoToolkit->getVideoThumbnailUrl($url);
    }

    public function getVideoEmbedCode(string $url): string
    {
        return new Markup(VideoToolkit::getInstance()->videoToolkit->getVideoEmbedCode($url), 'utf-8');
    }

    public function getVideoEmbedCodeResponsive(string $url, array $options = []): string
    {
        return new Markup(VideoToolkit::getInstance()->videoToolkit->getVideoEmbedCodeResponsive($url, $options), 'utf-8');
    }

}
