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
            if(isset($this->plugin->wasshrunk[strtolower($player->getName())]) && $this->plugin->wasshrunk[strtolower($player->getName())] <= time()){
                unset($this->plugin->wasshrunk[strtolower($player->getName())]);
            }
            foreach ($player->getInventory()->getArmorContents() as $armor) {
                $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::SHRINK);
                if ($enchantment !== null) {
                    $shrinkpoints++;
                }
            }
            if (isset($this->plugin->shrunk[strtolower($player->getName())]) && ($this->plugin->shrunk[strtolower($player->getName())] <= time() || $shrinkpoints < 4)) {
                unset($this->plugin->shrunk[strtolower($player->getName())]);
                $this->plugin->wasshrunk[strtolower($player->getName())] = time() + 1;
                $player->sendMessage(TextFormat::RED . "You have grown back to normal size.");
                $player->setScale(1);
            }
            foreach ($player->getInventory()->getArmorContents() as $armor) {
                $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::GROW);
                if ($enchantment !== null) {
                    $growpoints++;
                }
            }
            if (isset($this->plugin->grew[strtolower($player->getName())]) && ($this->plugin->grew[strtolower($player->getName())] <= time() || $growpoints < 4)) {
                unset($this->plugin->grew[strtolower($player->getName())]);
                $player->sendMessage(TextFormat::RED . "You have shrunk back to normal size.");
                $player->setScale(1);
            }
        }
    }
}