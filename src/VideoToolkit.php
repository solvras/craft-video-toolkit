<?php

namespace solvras\craftvideotoolkit;

use Craft;
use craft\base\Plugin;
use solvras\craftvideotoolkit\services\VideoToolkit as VideoToolkitAlias;

/**
 * Video Toolkit plugin
 *
 * @method static VideoToolkit getInstance()
 * @author Solvras <support@solvr.no>
 * @copyright Solvras
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read VideoToolkitAlias $videoToolkit
 */
class VideoToolkit extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public function init(): void
    {
        parent::init();

        $this->setComponents([
            'videoToolkit' => VideoToolkitAlias::class,
        ]);

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
