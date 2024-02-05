<?php

namespace solvras\craftvideotoolkit\video;

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
