<?php

namespace solvras\craftvideotoolkit\video;

class Youtube extends Video
{
    public function __construct(string $url, array $options = [])
    {
        parent::__construct($url, $options);
        $this->setKind('youtube');
        $this->setOembedData($this->getProviderData());
        $this->setId($this->getYoutubeId());
        $this->setRatio($this->calculateRatio());
        $this->setThumbnailUrl($this->getYoutubeThumbnailUrl());
        $this->setEmbedUrl($this->generateEmbedUrl());
        $this->setEmbedCode($this->getVideoEmbedCode());
    }

    public function getYoutubeId(): string|bool
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
        $result = preg_match($pattern, $this->getUrl(), $matches, );
        if (false !== $result) {
            return $matches[1];
        }
        return false;
    }

    public function generateEmbedUrl(): string // @TODO this is using default getter method, should we use a custom method?
    {
        if($this->getNoCookie()) {
            $embedUrl = 'https://www.youtube-nocookie.com/embed/';
        } else {
            $embedUrl = 'https://www.youtube.com/embed/';
        }
        if ($this->getId()) {
            return $embedUrl . $this->getId() . '?' . $this->getUrlParams();
        }
        return '';
    }

    public function getUrlParams(): string
    {
        $paramsArray = [];
        if($this->getMuted()) {
            $paramsArray = array_merge($paramsArray, ['mute' =>'1']);
        }
        if($this->getAutoplay()) {
            $paramsArray = array_merge($paramsArray, ['autoplay' =>'1']);
            $paramsArray = array_merge($paramsArray, ['mute' =>'1']);
        }
        if($this->getLoop()) {
            $paramsArray = array_merge($paramsArray, ['loop' =>'1']);
        }
        if(!$this->getControls()) {
            $paramsArray = array_merge($paramsArray, ['controls' =>'0']);
        }
        $paramsArray = array_merge($paramsArray, ['rel' =>'0']);
        return http_build_query($paramsArray);
    }

    public function getYoutubeThumbnailUrl(): string
    {
        return $this->getOembedData()->thumbnail_url;
    }

}