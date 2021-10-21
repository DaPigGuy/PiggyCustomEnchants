<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\tasks;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\plugin\ApiVersion;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;

class CheckUpdatesTask extends AsyncTask
{
    public function onRun(): void
    {
        $result = Internet::getURL("https://poggit.pmmp.io/releases.json?name=PiggyCustomEnchants", 10, [], $error);
        $this->setResult([$result?->getBody(), $error]);
    }

    public function onCompletion(): void
    {
        $plugin = CustomEnchantManager::getPlugin();
        [$body, $error] = $this->getResult();
        if ($error) {
            $plugin->getLogger()->warning("Auto-update check failed.");
            $plugin->getLogger()->debug($error);
        } else if ($plugin->isEnabled()) {
            $data = json_decode($body, true);
            if (version_compare($plugin->getDescription()->getVersion(), $data[0]["version"]) === -1) {
                if (ApiVersion::isCompatible($plugin->getServer()->getApiVersion(), $data[0]["api"][0])) {
                    $plugin->getLogger()->info("PiggyCustomEnchants v" . $data[0]["version"] . " is available for download at " . $data[0]["artifact_url"] . "/PiggyCustomEnchants.phar");
                }
            }
        }
    }
}