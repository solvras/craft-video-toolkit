<?php

namespace solvras\craftvideotoolkit\video;

use craft\elements\Asset;

class AssetVideo extends Video
{
    public function __construct(Asset $video, array $options = [])
    {
        if ($video->kind == 'video') {
            $videoUrl = $video->url;
        } else {
            $videoUrl = '';
        }
        parent::__construct($videoUrl, $options);
        $this->setKind('local');
        $this->setOembedData(false);
        $this->setRatio($this->calculateRatio());
        $this->setEmbedUrl($videoUrl);
        $this->setEmbedCode($this->getVideoTag());
    }
}
