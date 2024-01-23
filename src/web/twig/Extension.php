<?php

namespace solvras\craftvideotoolkit\web\twig;

use Craft;
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
            new TwigFunction('videoEmbedUrl', [$this, 'getEmbedUrl' ]),
            new TwigFunction('videoThumbnailUrl', [$this, 'getVideoThumbnailUrl' ]),
            new TwigFunction('videoEmbedCode', [$this, 'getVideoEmbedCode' ], ['is_safe' => ['html']]),
            new TwigFunction('videoEmbedCodeResponsive', [$this, 'getVideoEmbedCodeResponsive' ], ['is_safe' => ['html']]),

        ];
    }

    public function getEmbedUrl($url): string
    {
        return VideoToolkit::getInstance()->videoToolkit->getEmbedUrl($url);
    }

    public function getVideoThumbnailUrl($url): string
    {
        return VideoToolkit::getInstance()->videoToolkit->getVideoThumbnailUrl($url);
    }

    public function getVideoEmbedCode($url): string
    {
        return new Markup(VideoToolkit::getInstance()->videoToolkit->getVideoEmbedCode($url), 'utf-8');
    }

    public function getVideoEmbedCodeResponsive($url, $options): string
    {
        return new Markup(VideoToolkit::getInstance()->videoToolkit->getVideoEmbedCodeResponsive($url, $options), 'utf-8');
    }

}