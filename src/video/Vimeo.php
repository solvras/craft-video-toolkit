<?php

namespace solvras\craftvideotoolkit\video;

class Vimeo extends Video
{
    protected string|false $privateId;
    public function __construct(string $url, array $options = [])
    {
        parent::__construct($url, $options);
        $this->setKind('vimeo');
        $this->setOembedData($this->getProviderData());
        if($this->getOembedData()) {
            $this->setId($this->getVimeoId());
            $this->setPrivateId($this->getVimeoPrivateId());
            $this->setRatio($this->calculateRatio());
            if (!$this->getThumbnailUrl()) {
                $this->setThumbnailUrl($this->getVimeoThumbnailUrl());
            }
            $this->setEmbedUrl($this->generateEmbedUrl());
            $this->setEmbedCode($this->getVideoEmbedCode());
        }
    }

    public function getVimeoId(): string|bool
    {
        return $this->getOembedData()->video_id;
    }

    public function getVimeoPrivateId(): string|bool
    {
        $uri = $this->getOembedData()->uri;
        $idString = explode('/', $uri)[2];
        $idArray = explode(":",$idString);
        if (count($idArray) > 1) {
            return $idArray[1];
        }
        //return explode(":",explode('/', $uri)[1])[1];
        return false;
    }

    public function generateEmbedUrl(): string // @TODO this is using default getter method, should we use a custom method?
    {
        if ($this->getId()) {
            $params = $this->getUrlParams();
            return 'https://player.vimeo.com/video/' . $this->getId() . '?' . $params;
        }
        return '';
    }

    public function getUrlParams(): string
    {
        $paramsArray = [];
        if ($this->getVimeoPrivateId()) {
            $paramsArray = array_merge($paramsArray, ['h' => $this->getVimeoPrivateId()]);
        }
        if ($this->getNoCookie()) {
            $paramsArray = array_merge($paramsArray, ['dnt' => '1']);
        }
        if ($this->getMuted()) {
            $paramsArray = array_merge($paramsArray, ['muted' => '1']);
        }
        if ($this->getAutoplay()) {
            $paramsArray = array_merge($paramsArray, ['autoplay' => '1']);
            $paramsArray = array_merge($paramsArray, ['muted' => '1']);
        }
        if ($this->getLoop()) {
            $paramsArray = array_merge($paramsArray, ['loop' => '1']);
        }
        if (!$this->getControls()) {
            $paramsArray = array_merge($paramsArray, ['background' => '1']);
        }
        return http_build_query($paramsArray);
    }

    public function getVimeoThumbnailUrl(): string
    {
        return $this->getOembedData()->thumbnail_url ?? '';
    }

    public function getPrivateId(): string
    {
        return $this->privateId;
    }

    public function setPrivateId(string $privateId): void
    {
        $this->privateId = $privateId;
    }
}
