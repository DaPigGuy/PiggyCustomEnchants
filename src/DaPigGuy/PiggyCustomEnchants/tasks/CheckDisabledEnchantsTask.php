<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\tasks;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckDisabledEnchantsTask extends AsyncTask
{
    public function onRun(): void
    {
        //TODO: Pass through proxy w/ API & Plugin Version for statistics on plugin
        $disabledEnchants = Internet::getURL("https://gist.githubusercontent.com/DaPigGuy/9c65a998bc0aa8d6b4708796110f7d11/raw/");
        if ($disabledEnchants !== false) {
            $this->setResult($disabledEnchants);
        }
    }

    public function onCompletion(Server $server): void
    {
        if ($this->getResult() !== null) {
            $plugin = CustomEnchantManager::getPlugin();
            if ($plugin->isEnabled()) {
                $disabledEnchants = json_decode($this->getResult(), true);
                foreach ($disabledEnchants as $disabledEnchantEntry) {
                    if (
                        count(array_intersect($disabledEnchantEntry["api"], $plugin->getDescription()->getCompatibleApis())) > 0 ||
                        in_array("all", $disabledEnchantEntry["api"]) ||
                        in_array($plugin->getDescription()->getVersion(), $disabledEnchantEntry["version"]) ||
                        in_array("all", $disabledEnchantEntry["version"])
                    ) {
                        $plugin->getLogger()->info("Enchantment " . $disabledEnchantEntry["name"] . " (id " . $disabledEnchantEntry["id"] . ") has been remotely disabled for " . $disabledEnchantEntry["reason"]);
                        CustomEnchantManager::unregisterEnchantment($disabledEnchantEntry["id"]);
                    }
                }
            }
        }
    }
}