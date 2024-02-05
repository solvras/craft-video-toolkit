<?php

namespace solvras\craftvideotoolkit\video;

class LocalPath extends Video
{
    public function __construct(string $videoPath, array $options = [])
    {
        parent::__construct($videoPath, $options);
        $this->setKind('local');
        $this->setOembedData(false);
        $this->setRatio($this->calculateRatio());
        $this->setEmbedUrl($videoPath);
        $this->setEmbedCode($this->getVideoTag());
    }
}
