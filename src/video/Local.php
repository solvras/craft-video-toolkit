<?php

namespace solvras\craftvideotoolkit\video;

use solvras\craftvideotoolkit\video\Video;

class Local extends Video
{
    public function __construct(string $url, array $options = [])
    {
        parent::__construct($url, $options);
        $this->setKind('local');
        $this->setOembedData(null);
        $this->setId(null);
        $this->setRatio(9/16);
        $this->setThumbnailUrl(null);
        $this->setEmbedUrl($this->getEmbedUrl());
        $this->setEmbedCode($this->getVideoEmbedCode());
    }
}