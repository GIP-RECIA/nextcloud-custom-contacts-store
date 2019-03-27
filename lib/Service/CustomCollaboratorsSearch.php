<?php

namespace OCA\ReciaCustom\Service;

use OC\Collaboration\Collaborators\Search;

class CustomCollaboratorsSearch extends Search
{
    public function unregisterPlugins($shareType) {
        $this->pluginList[$shareType] = [];
    }
}