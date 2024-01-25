<?php

namespace solvras\craftvideotoolkit\video;

use Craft;
use Twig\Markup;

class Video
{
    protected const WIDTH = 640;
    protected const HEIGHT = 360;
    private string $id;
    private string $url;
    private string $kind = 'local';
    private int $height;
    private int $width;
    private float $ratio;
    private bool $autoplay;
    private bool $muted;
    private bool $loop;
    private bool $controls;
    private bool $noCookie;
    private bool $responsive;
    private bool $useProviderRatio;
    private bool $useStyles;
    private array $customClasses;
    private array $customCss;
    private string $embedUrl;
    private string $thumbnailUrl;
    private string $embedCode;
    private string $embedCodeResponsive;
    private string $title;
    private \stdClass|false $oembedData;

    private array $providerOembedUrls = [
        'youtube' => 'https://www.youtube.com/oembed?url=',
        'vimeo' => 'https://vimeo.com/api/oembed.json?url=',
        //'dailyMotion' => 'https://www.dailymotion.com/services/oembed?url=', // not tested
        //'twitch' => 'https://api.twitch.tv/v4/oembed?url=', // not tested
        //'facebook' => 'https://www.facebook.com/plugins/video/oembed.json/?url=', // not tested
        //'wistia' => 'https://fast.wistia.com/oembed?url=', // not tested
        //'tiktok' => 'https://www.tiktok.com/oembed?url=', // not tested
    ];
    public function __construct(string $url, array $options = [])
    {
        $this->setUrl($url);
        $this->setOptions($options);
    }

    // helpers
    public function getProviderData()
    {
        if($this->isUrl($this->url)) {
            $cache = Craft::$app->getCache(); // @TODO add use cache to plugin settings
            $cacheKey = 'videoToolkit-' . $this->url; // @TODO add cache key to plugin settings
            $cacheDuration = 60 * 60 * 24 * 30; // 30 days // @TODO add cache duration to plugin settings
            $oembed = $cache->get($cacheKey);
            if ($oembed === false) {
                if ($oembedUrl = $this->getProviderOembedUrl()) {
                    $oembedUrl .= $this->url;
                    $oembed = json_decode(file_get_contents($oembedUrl));
                    $cache->set($cacheKey, $oembed, $cacheDuration);
                    return $oembed;
                } else {
                    return false;
                }
            } else {
                return $oembed;
            }

        } else {
            // local file
            return false;
        }
    }

    public function isUrl($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    private function getProviderOembedUrl(): string
    {
        return $this->providerOembedUrls[$this->kind] ?? '';
    }


    public static function getVideoKind($url): string
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

    public function html(): string
    {
        if($this->getResponsive() && $this->getEmbedCode()) {
            return $this->getResponsiveEmbedCode();
        } else {
            return $this->getEmbedCode();
        }
    }

    public function thumbnail(): string
    {
        if($this->getThumbnailUrl()) {
            return new Markup('<img src="' . $this->getThumbnailUrl() . '" alt="" />', 'utf-8');
            //return '<img src="' . $this->getThumbnailUrl() . '" alt="" />';
        }
        return '';
    }

    public function getVideoEmbedCode(): string
    {
        $width = $this->getWidth();
        if($this->getUseProviderRatio()) {
            $height = $this->getWidth() * $this->getRatio();
        } else {
            $height = $this->getHeight();
        }

        $style = [];
        if($this->getResponsive()) {
            $attrSize = "";
            $style = array_merge($style, ['width' => '100%', 'height' => '100%', 'border' => '0']);
        } else {
            $attrSize = "width='" . $width . "' height='" . $height . "'";
            $style = array_merge($style, ['border' => '0']);
        }
        $customCss = $this->getCustomCss();
        if(array_key_exists('iframe', $customCss)) {
            $style = array_merge($style, $customCss['iframe']);
        }

        $styleSize = 'style="' . $this->implodeStyles($style) . '"';

        if ($this->getEmbedUrl()) {
            return '<iframe src="' . $this->getEmbedUrl() . '" ' . $attrSize . ' ' . $styleSize . ' allow="autoplay; fullscreen; encrypted-media;" allowfullscreen></iframe>';
        }
        return '';
    }

    private function getResponsiveEmbedCode(): string
    {
        $wrapperClass = [];
        $wrapperInnerClass = [];
        $wrapperStyle = [];
        $wrapperInnerStyle = [];

        $customClasses = $this->getCustomClasses();
        $customCss = $this->getCustomCss();
        if(count($customClasses) > 0) {
            $wrapperClass = array_merge($wrapperClass, $customClasses['wrapper']);
            $wrapperInnerClass = array_merge($wrapperInnerClass, $customClasses['wrapperInner']);
        }
        if($this->getUseStyles()) {
            $wrapperStyle = array_merge($wrapperStyle, $this->getVideoWrapperCss());
            $wrapperInnerStyle = array_merge($wrapperInnerStyle, $this->getVideoWrapperInnerCss());
            if($this->getRatio()) {
                $wrapperStyle['padding-bottom'] = $this->getRatio() * 100 . '%';
            }
        }
        if($customCss) {
            if($customCss['wrapper']) {
                $wrapperStyle = array_merge($wrapperStyle, $customCss['wrapper']);
            }
            if (array_key_exists('wrapperInner', $customCss)) {
                $wrapperInnerStyle = array_merge($wrapperInnerStyle, $customCss['wrapperInner']);
            }

        }
        $wrapperClassString = implode(' ', $wrapperClass);
        $wrapperInnerClassString = implode(' ', $wrapperInnerClass);
        $wrapperStyleString = $this->implodeStyles($wrapperStyle);
        $wrapperInnerStyleString = $this->implodeStyles($wrapperInnerStyle);
        $wrapperClassStyle = [];
        $wrapperInnerClassStyle = [];
        if($wrapperClassString) {
            $wrapperClassStyle[] = 'class="' . $wrapperClassString . '"';
        }
        if($wrapperInnerClassString) {
            $wrapperInnerClassStyle[] = 'class="' . $wrapperInnerClassString . '"';
        }
        if($wrapperStyleString) {
            $wrapperClassStyle[] = 'style="' . $wrapperStyleString . '"';
        }
        if($wrapperInnerStyleString) {
            $wrapperInnerClassStyle[] = 'style="' . $wrapperInnerStyleString . '"';
        }
        $embedCode = $this->getEmbedCode();
        if ($embedCode) {
            return '<div '. implode(' ', $wrapperClassStyle) .'><div '. implode(' ', $wrapperInnerClassStyle) .'>' . $embedCode . '</div></div>';
        }
        return '';
    }

    public function setOptions(array $options): void
    {
        $autoplay = $options['autoplay'] ?? false;
        $muted = $options['muted'] ?? false;
        $loop = $options['loop'] ?? false;
        $controls = $options['controls'] ?? true;
        $noCookie = $options['noCookie'] ?? false;
        $responsive = $options['responsive'] ?? false;
        $useProviderRatio = $options['useProviderRatio'] ?? false;
        $useStyles = $options['useStyles'] ?? true;
        $customClasses = $options['customClasses'] ?? [];
        $customCss = $options['customCss'] ?? [];
        $width = $options['width'] ?? self::WIDTH;
        $height = $options['height'] ?? self::HEIGHT;
        $this->setAutoplay($autoplay);
        $this->setMuted($muted);
        $this->setLoop($loop);
        $this->setControls($controls);
        $this->setNoCookie($noCookie);
        $this->setResponsive($responsive);
        $this->setUseProviderRatio($useProviderRatio);
        $this->setUseStyles($useStyles);
        $this->setCustomClasses($customClasses);
        $this->setCustomCss($customCss);
        $this->setWidth($width);
        $this->setHeight($height);
    }

    /**
     * @return float
     */
    public function calculateRatio(): float
    {
        if ($this->getOembedData()) {
            $ratio = $this->getOembedData()->height / $this->getOembedData()->width;
        } else {
            $ratio = $this->getHeight() / $this->getWidth();
        }
        return $ratio;
    }

    public function getVideoWrapperCss(): array
    {
        //return 'position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;';
        return [
            'position' => 'relative',
            'padding-bottom' => '56.25%',
            'height' => '0',
            'overflow' => 'hidden',
        ];
    }

    // get video wrapper inner css code for inline styling
    public function getVideoWrapperInnerCss(): array
    {
        return [
            'position' => 'absolute',
            'top' => '0',
            'left' => '0',
            'width' => '100%',
            'height' => '100%'
        ];
    }

    public function implodeStyles(array $styles): string
    {
        $styleString = '';
        foreach($styles as $key => $value) {
            $styleString .= $key . ':' . $value . ';';
        }
        return $styleString;
    }

    // Setters
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @param string $kind
     * @return void
     */
    public function setKind(string $kind): void
    {
        $this->kind = $kind;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function setRatio(float $ratio): void
    {
        $this->ratio = $ratio;
    }

    public function setAutoplay(bool $autoplay): void
    {
        $this->autoplay = $autoplay;
    }

    public function setMuted(bool $muted): void
    {
        $this->muted = $muted;
    }

    public function setLoop(bool $loop): void
    {
        $this->loop = $loop;
    }

    public function setControls(bool $controls): void
    {
        $this->controls = $controls;
    }

    public function setNoCookie(bool $noCookie): void
    {
        $this->noCookie = $noCookie;
    }

    public function setResponsive(bool $responsive): void
    {
        $this->responsive = $responsive;
    }

    public function setUseProviderRatio(bool $useProviderRatio): void
    {
        $this->useProviderRatio = $useProviderRatio;
    }

    public function setUseStyles(bool $useStyles): void
    {
        $this->useStyles = $useStyles;
    }

    public function setCustomClasses(array $customClasses): void
    {
        $this->customClasses = $customClasses;
    }

    public function setCustomCss(array $customCss): void
    {
        $this->customCss = $customCss;
    }
    // This might not be needed
    public function setEmbedUrl(string $embedUrl): void
    {
        $this->embedUrl = $embedUrl;
    }
    // This might not be needed
    public function setThumbnailUrl(string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }
    // This might not be needed
    public function setEmbedCode(string $embedCode): void
    {
        $this->embedCode = $embedCode;
    }
    // This might not be needed
    public function setEmbedCodeResponsive(string $embedCodeResponsive): void
    {
        $this->embedCodeResponsive = $embedCodeResponsive;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setOembedData($oembedData): void
    {
        $this->oembedData = $oembedData;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function getAutoplay(): bool
    {
        return $this->autoplay;
    }

    public function getMuted(): bool
    {
        return $this->muted;
    }

    public function getLoop(): bool
    {
        return $this->loop;
    }

    public function getControls(): bool
    {
        return $this->controls;
    }

    public function getNoCookie(): bool
    {
        return $this->noCookie;
    }

    public function getResponsive(): bool
    {
        return $this->responsive;
    }

    public function getUseProviderRatio(): bool
    {
        return $this->useProviderRatio;
    }

    public function getUseStyles(): bool
    {
        return $this->useStyles;
    }

    public function getCustomClasses(): array
    {
        return $this->customClasses;
    }

    public function getCustomCss(): array
    {
        return $this->customCss;
    }

    public function getEmbedUrl(): string
    {
        return $this->embedUrl;
    }

    public function getThumbnailUrl(): string
    {
        return $this->thumbnailUrl;
    }

    public function getEmbedCode(): string
    {
        return $this->embedCode;
    }

    public function getEmbedCodeResponsive(): string
    {
        return $this->embedCodeResponsive;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getOembedData(): \stdClass|false
    {
        return $this->oembedData;
    }

    public function __toString(): string
    {
        return $this->html();
    }

}