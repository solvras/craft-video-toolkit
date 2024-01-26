<?php

namespace solvras\craftvideotoolkit\video;

use craft\elements\Asset;
use solvras\craftvideotoolkit\video\Video;

class UrlVideo extends Video
{
    public function __construct(string $videoUrl, array $options = [])
    {
        parent::__construct($videoUrl, $options);
        $this->setKind('local');
        $this->setOembedData(false);
        $this->setRatio($this->calculateRatio());
        $this->setEmbedUrl($videoUrl);
        $this->setEmbedCode($this->getVideoTag());
    }


}