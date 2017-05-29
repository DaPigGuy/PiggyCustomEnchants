<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

/**
 * Class SizeTask
 * @package PiggyCustomEnchants
 */
class SizeTask extends PluginTask
{
    private $plugin;
    private $radars;

    /**
     * SizeTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    /**
     * @param $currentTick
     */
    public function onRun($currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $shrinkpoints = 0;
            $growpoints = 0;
            if (isset($this->plugin->sizemanipulated[strtolower($player->getName())]) && $this->plugin->sizemanipulated[strtolower($player->getName())] <= time()) {
                unset($this->plugin->sizemanipulated[strtolower($player->getName())]);
            }
            foreach ($player->getInventory()->getArmorContents() as $armor) {
                $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::SHRINK);
                if ($enchantment !== null) {
                    $shrinkpoints++;
                }
            }
            if (isset($this->plugin->shrunk[strtolower($player->getName())]) && ($this->plugin->shrunk[strtolower($player->getName())] <= time() || $shrinkpoints < 4)) {
                if ($this->plugin->shrunk[strtolower($player->getName())] > time()) {
                    $this->plugin->shrinkremaining[strtolower($player->getName())] = $this->plugin->shrunk[strtolower($player->getName())] - time();
                    unset($this->plugin->shrinkcd[strtolower($player->getName())]);
                }
                unset($this->plugin->shrunk[strtolower($player->getName())]);
                $this->plugin->sizemanipulated[strtolower($player->getName())] = time() + 5;
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have grown back to normal size.");
            }
            foreach ($player->getInventory()->getArmorContents() as $armor) {
                $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::GROW);
                if ($enchantment !== null) {
                    $growpoints++;
                }
            }
            if (isset($this->plugin->grew[strtolower($player->getName())]) && ($this->plugin->grew[strtolower($player->getName())] <= time() || $growpoints < 4)) {
                if ($this->plugin->grew[strtolower($player->getName())] > time()) {
                    $this->plugin->growremaining[strtolower($player->getName())] = $this->plugin->grew[strtolower($player->getName())] - time();
                    unset($this->plugin->growcd[strtolower($player->getName())]);
                }
                unset($this->plugin->grew[strtolower($player->getName())]);
                $this->plugin->sizemanipulated[strtolower($player->getName())] = time() + 5;
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
            }
        }
    }
}