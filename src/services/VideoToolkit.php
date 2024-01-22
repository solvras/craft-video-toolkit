<?php

namespace solvras\craftvideotoolkit\services;

use Craft;
use yii\base\Component;

/**
 * Video Toolkit service
 */
class VideoToolkit extends Component
{
    private const WIDTH = 640;
    private const HEIGHT = 480;
    // get id from YouTube video
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

    // get vimeo private id
    public function getVimeoPrivateId($url): string
    {
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['path'])) {
            $path = explode('/', $parsedUrl['path']);
            if (isset($path[2])) {
                return $path[2];
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
            $privateAttr = '';
            if($privateId = $this->getVimeoPrivateId($url)) {
                $privateAttr = "?h=" . $privateId;
            }
            return 'https://player.vimeo.com/video/' . $id . $privateAttr;
        }
        return '';
    }

    public function getEmbedUrl($url): string
    {
        $kind = $this->getVideoKind($url);
        if ($kind == 'youtube') {
            return $this->getYoutubeEmbedUrl($url);
        } elseif ($kind == 'vimeo') {
            return $this->getVimeoEmbedUrl($url);
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

    // get video thumbnail url
    public function getVideoThumbnailUrl($url): string
    {
        $kind = $this->getVideoKind($url);
        if ($kind == 'youtube') {
            return $this->getYoutubeThumbnailUrl($url);
        } elseif ($kind == 'vimeo') {
            return $this->getVimeoThumbnailUrl($url);
        }
        return '';
    }

    // get youtube embed code
    // @TODO add autoplay option
    // @TODO add cookie setting
    // @TODO add size option, either fixed or responsive
    public function getYoutubeEmbedCode($url, $options = []): string
    {
        $id = $this->getYoutubeId($url);
        if ($id) {
            return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
        return '';
    }

    // get vimeo embed code
    // @TODO add cookie setting
    // @TODO add size option, either fixed or responsive
    // @TODO write in documentation that videos will be muted when autoplay is true
    public function getVimeoEmbedCode($url, $options = []): string
    {
        $muted = $options['muted'] ?? false;
        $autoplay = $options['autoplay'] ?? false;
        $loop = $options['loop'] ?? false;
        $controls = $options['controls'] ?? true;
        $height = $options['height'] ?? self::HEIGHT;
        $width = $options['width'] ?? self::WIDTH;
        $responsive = $options['responsive'] ?? false;

        $attrArray = [];
        if($privateId = $this->getVimeoPrivateId($url)) {
            $attrArray = array_merge($attrArray, ['h' => $privateId]);
        }
        if($muted) {
            $attrArray = array_merge($attrArray, ['muted' =>'1']);
        }
        if($autoplay) {
            $attrArray = array_merge($attrArray, ['autoplay' =>'1']);
            $attrArray = array_merge($attrArray, ['muted' =>'1']);
        }
        if($loop) {
            $attrArray = array_merge($attrArray, ['loop' =>'1']);
        }
        if(!$controls) {
            $attrArray = array_merge($attrArray, ['background' =>'1']);
        }


        if($responsive) {
            $attrSize = "";
            $styleSize = "style='width:100%;height:100%;'";
        } else {
            $attrSize = "width='" . $width . "' height='" . $height . "'";
            $styleSize = "";
        }

        $id = $this->getVimeoId($url);
        if ($id) {
            return '<iframe src="https://player.vimeo.com/video/' . $id . '?'. http_build_query($attrArray) . '" ' . $attrSize . ' ' . $styleSize . ' frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
        }
        return '';
    }

    // get local video embed code
    // @TODO check other formats than mp4
    // @TODO write in documentation that videos will be muted when autoplay is true
    public function getLocalVideoEmbedCode($url, $options = []): string
    {
        $muted = $options['muted'] ?? false;
        $autoplay = $options['autoplay'] ?? false;
        $loop = $options['loop'] ?? false;
        $controls = $options['controls'] ?? true;
        $attrArray = [];
        if($muted) {
            $attrArray[] = 'muted';
        }
        if($autoplay) {
            $attrArray[] = 'autoplay';
            $attrArray[] = 'muted';
        }
        if($loop) {
            $attrArray[] = 'loop';
        }
        if($controls) {
            $attrArray[] = 'controls';
        }

        if ($url) {
            return '<video ' . implode('',$attrArray) . '><source src="' . $url . '" type="video/mp4"></video>';
        }
        return '';
    }

    // check if YouTube, vimeo video or local, return kind
    public function getVideoKind($url): string
    {
        if (str_contains($url, 'youtube')) {
            return 'youtube';
        } elseif (str_contains($url, 'vimeo')) {
            return 'vimeo';
        } elseif (!str_contains($url, 'http')) {
            return 'local';
        }
        return '';
    }

    // get video embed code
    public function getVideoEmbedCode($url, $options = []): string
    {
        $kind = $this->getVideoKind($url);
        if ($kind == 'youtube') {
            return $this->getYoutubeEmbedCode($url);
        } elseif ($kind == 'vimeo') {
            return $this->getVimeoEmbedCode($url, $options);
        } elseif ($kind == 'local') {
            return $this->getLocalVideoEmbedCode($url);
        }
        return '';
    }

    // get video embed code with responsive wrapper with correct css styles
    public function getVideoEmbedCodeResponsive($url, $options = []): string
    {
        $customClasses = $options['customClasses'] ?? null;
        $customCss = $options['customCss'] ?? null;
        $useTailwind = $options['useTailwind'] ?? false;
        $useStyles = $options['useStyles'] ?? true; // this is default

        $wrapperClass = [];
        $wrapperInnerClass = [];
        $wrapperStyle = [];
        $wrapperInnerStyle = [];
        if($useTailwind) {
            $wrapperClass = array_merge($wrapperClass, $this->getVideoWrapperTailwind());
            $wrapperInnerClass = array_merge($wrapperInnerClass, $this->getVideoWrapperInnerTailwind());
        }
        if($customClasses) {
            $wrapperClass = array_merge($wrapperClass, $customClasses['wrapper']);
            $wrapperInnerClass = array_merge($wrapperInnerClass, $customClasses['wrapperInner']);
        }
        if($useStyles) {
            $wrapperStyle = array_merge($wrapperStyle, $this->getVideoWrapperCss());
            $wrapperInnerStyle = array_merge($wrapperInnerStyle, $this->getVideoWrapperInnerCss());
        }
        if($customCss) {
            $wrapperStyle = array_merge($wrapperStyle, $customCss['wrapper']);
            $wrapperInnerStyle = array_merge($wrapperInnerStyle, $customCss['wrapperInner']);
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
        $options['responsive'] = true;
        $embedCode = $this->getVideoEmbedCode($url, $options);
        if ($embedCode) {
            return '<div '. implode(' ', $wrapperClassStyle) .'><div '. implode(' ', $wrapperInnerClassStyle) .'>' . $embedCode . '</div></div>';
        }
        return '';
    }

    // get video wrapper css code for inline styling
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

    // get video wrapper styling as tailwind classes
    public function getVideoWrapperTailwind(): array
    {
        return [
            'relative',
            'pb-[56.25%]',
            'h-0',
            'overflow-hidden'
        ];
    }

    // get video wrapper inner styling as tailwind classes
    public function getVideoWrapperInnerTailwind(): array
    {
        return [
            'absolute',
            'top-0',
            'left-0',
            'w-full',
            'h-full'
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

}
