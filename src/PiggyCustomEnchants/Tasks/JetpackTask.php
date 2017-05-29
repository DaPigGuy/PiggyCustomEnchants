<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

/**
 * Class JetpackTask
 * @package PiggyCustomEnchants\Tasks
 */
class JetpackTask extends PluginTask
{
    private $plugin;

    /**
     * JetpackTask constructor.
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
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getBoots(), CustomEnchants::JETPACK);
            if ($enchantment !== null) {
                if (isset($this->plugin->flying[strtolower($player->getName())]) && $this->plugin->flying[strtolower($player->getName())] > time()) {
                    if ($this->plugin->flying[strtolower($player->getName())] - 30 <= time()) {
                        $player->sendTip(TextFormat::RED . "Low on power. " . floor($this->plugin->flying[strtolower($player->getName())] - time()) . " seconds of power remaining.");
                    }
                    $this->fly($player, $enchantment->getLevel());
                    continue;
                }
            }
            if (isset($this->plugin->flying[strtolower($player->getName())])) {
                if ($this->plugin->flying[strtolower($player->getName())] > time()) {
                    $this->plugin->flyremaining[strtolower($player->getName())] = $this->plugin->flying[strtolower($player->getName())] - time();
                    unset($this->plugin->jetpackcd[strtolower($player->getName())]);
                }
                unset($this->plugin->flying[strtolower($player->getName())]);
                $player->sendTip(TextFormat::RED . "Jetpack disabled.");
            }
        }
    }

    /**
     * @param Player $player
     * @param $level
     */
    public function fly(Player $player, $level)
    {
        $player->setMotion($player->getDirectionVector()->multiply($level));
        $player->getLevel()->addParticle(new FlameParticle($player));
    }
}