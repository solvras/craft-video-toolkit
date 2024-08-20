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
    private bool $poster;
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
        $this->setEmbedCode('');
        $this->setUrl($url);
        $this->setOptions($options);
    }

    // helpers
    public function getProviderData(): \stdClass|false
    {
        $cacheSettings = $this->getCacheSettings();
        if ($this->isUrl($this->url)) {
            if ($cacheSettings['cacheEnabled']) {
                $cache = Craft::$app->getCache();
                $cacheKey = $cacheSettings['cachePrefix'] . '-' . $this->url;
                $cacheDuration = $cacheSettings['cacheDuration'];
                $oembed = $cache->get($cacheKey);
                if ($oembed === false) {
                    if ($oembed = $this->getOembedDataFromProvider()) {
                        $cache->set($cacheKey, $oembed, $cacheDuration);
                        return $oembed;
                    } else {
                        return false;
                    }
                } else {
                    return $oembed;
                }
            } else {
                return $this->getOembedDataFromProvider();
            }
        } else {
            // local file
            return false;
        }
    }

    public function getOembedDataFromProvider(): \stdClass|false
    {
        if ($oembedUrl = $this->getProviderOembedUrl()) {
            $oembedUrl .= $this->url;
    
            $return = json_decode($this->get_content($oembedUrl));

            if($return) {
                return $return;
            }
        
        }
        return false;
    }

    function get_content($URL){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function isUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    private function getProviderOembedUrl(): string
    {
        return $this->providerOembedUrls[$this->kind] ?? '';
    }

    public function html(): string
    {
        if ($this->getResponsive() && $this->getEmbedCode()) {
            return $this->getResponsiveEmbedCode();
        } else {
            return $this->getEmbedCode();
        }
    }

    public function thumbnail(): string
    {
        if ($this->getThumbnailUrl()) {
            return new Markup('<img src="' . $this->getThumbnailUrl() . '" alt="" />', 'utf-8');
            //return '<img src="' . $this->getThumbnailUrl() . '" alt="" />';
        }
        return '';
    }

    public function getVideoEmbedCode(): string
    {
        $width = $this->getWidth();
        if ($this->getUseProviderRatio()) {
            $height = $this->getWidth() * $this->getRatio();
        } else {
            $height = $this->getHeight();
        }

        $style = [];
        if ($this->getResponsive()) {
            $attrSize = "";
            $style = array_merge($style, ['width' => '100%', 'height' => '100%', 'border' => '0']);
        } else {
            $attrSize = "width='" . $width . "' height='" . $height . "'";
            $style = array_merge($style, ['border' => '0']);
        }
        $customCss = $this->getCustomCss();
        if (array_key_exists('iframe', $customCss)) {
            $style = array_merge($style, $customCss['iframe']);
        }

        $styleSize = 'style="' . $this->implodeStyles($style) . '"';

        if ($this->getEmbedUrl()) {
            return '<iframe src="' . $this->getEmbedUrl() . '" ' . $attrSize . ' ' . $styleSize . ' allow="autoplay; fullscreen; encrypted-media;" allowfullscreen></iframe>';
        }
        return '';
    }

    public function getVideoTag(): string
    {
        $attrArray = [];
        if ($this->getMuted()) {
            $attrArray[] = 'muted';
        }
        if ($this->getAutoplay()) {
            $attrArray[] = 'autoplay';
            $attrArray[] = 'muted';
        }
        if ($this->getLoop()) {
            $attrArray[] = 'loop';
        }
        if ($this->getControls()) {
            $attrArray[] = 'controls';
        }

        if ($this->getPoster()) {
            $attrArray[] = 'poster="' . $this->getThumbnailUrl() . '"';
        }

        if ($this->getResponsive()) {
            $attrSize = "";
            $styleSize = "style='width:100%;height:100%;'";
        } else {
            $attrSize = "width='" . $this->getWidth() . "' height='" . $this->getHeight() . "'";
            $styleSize = "";
        }

        if ($this->getUrl()) {
            return '<video ' . implode(' ', $attrArray) . ' ' . $attrSize . ' ' . $styleSize . '><source src="' . $this->getUrl() . '" type="video/mp4"></video>';
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
        if (count($customClasses) > 0) {
            $wrapperClass = array_merge($wrapperClass, $customClasses['wrapper']);
            $wrapperInnerClass = array_merge($wrapperInnerClass, $customClasses['wrapperInner']);
        }
        if ($this->getUseStyles()) {
            $wrapperStyle = array_merge($wrapperStyle, $this->getVideoWrapperCss());
            $wrapperInnerStyle = array_merge($wrapperInnerStyle, $this->getVideoWrapperInnerCss());
            if ($this->getRatio()) {
                $wrapperStyle['padding-bottom'] = $this->getRatio() * 100 . '%';
            }
        }
        if ($customCss) {
            if ($customCss['wrapper']) {
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
        if ($wrapperClassString) {
            $wrapperClassStyle[] = 'class="' . $wrapperClassString . '"';
        }
        if ($wrapperInnerClassString) {
            $wrapperInnerClassStyle[] = 'class="' . $wrapperInnerClassString . '"';
        }
        if ($wrapperStyleString) {
            $wrapperClassStyle[] = 'style="' . $wrapperStyleString . '"';
        }
        if ($wrapperInnerStyleString) {
            $wrapperInnerClassStyle[] = 'style="' . $wrapperInnerStyleString . '"';
        }
        $embedCode = $this->getEmbedCode();
        if ($embedCode) {
            return '<div ' . implode(' ', $wrapperClassStyle) . '><div ' . implode(' ', $wrapperInnerClassStyle) . '>' . $embedCode . '</div></div>';
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
        $poster = $options['poster'] ?? false;
        $useProviderRatio = $options['useProviderRatio'] ?? false;
        $useStyles = $options['useStyles'] ?? true;
        $customClasses = $options['customClasses'] ?? [];
        $customCss = $options['customCss'] ?? [];
        $width = $options['width'] ?? self::WIDTH;
        $height = $options['height'] ?? self::HEIGHT;
        $thumbnailUrl = $options['thumbnailUrl'] ?? '';
        $this->setAutoplay($autoplay);
        $this->setMuted($muted);
        $this->setLoop($loop);
        $this->setControls($controls);
        $this->setNoCookie($noCookie);
        $this->setResponsive($responsive);
        $this->setPoster($poster);
        $this->setUseProviderRatio($useProviderRatio);
        $this->setUseStyles($useStyles);
        $this->setCustomClasses($customClasses);
        $this->setCustomCss($customCss);
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setThumbnailUrl($thumbnailUrl);
    }

    public function getCacheSettings(): array
    {
        $config = Craft::$app->config->getConfigFromFile('video-toolkit');
        $cacheConfig = $config['cache'] ?? [];

        return [
            'cacheEnabled' => $cacheConfig['enabled'] ?? true,
            'cacheDuration' => $cacheConfig['duration'] ?? 3600,
            'cachePrefix' => $cacheConfig['prefix'] ?? 'video-toolkit',
        ];
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
            'height' => '100%',
        ];
    }

    public function implodeStyles(array $styles): string
    {
        $styleString = '';
        foreach ($styles as $key => $value) {
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

    public function setPoster(bool $poster): void
    {
        $this->poster = $poster;
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

    public function getPoster(): bool
    {
        return $this->poster;
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
