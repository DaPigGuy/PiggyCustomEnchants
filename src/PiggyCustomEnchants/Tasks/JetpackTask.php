<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\level\particle\FlameParticle;
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
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getBoots(), CustomEnchants::JETPACK);
            if ($enchantment !== null) {
                if (isset($this->plugin->flying[strtolower($player->getName())]) && $this->plugin->flying[strtolower($player->getName())] > time()) {
                    if ($this->plugin->flying[strtolower($player->getName())] - 30 <= time()) {
                        $player->sendTip(TextFormat::RED . "Low on power. " . floor($this->plugin->flying[strtolower($player->getName())] - time()) . " seconds of power remaining.");
                    } else {
                        $time = ($this->plugin->flying[strtolower($player->getName())] - time());
                        $time = is_float($time / 15) ? floor($time / 15) + 1 : $time / 15;
                        $color = $time > 10 ? TextFormat::GREEN : $time > 5 ? TextFormat::YELLOW : TextFormat::RED;
                        $player->sendTip($color . "Power: " . str_repeat("▌", $time));
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
            if (isset($this->plugin->flyremaining[strtolower($player->getName())])) {
                if ($this->plugin->flyremaining[strtolower($player->getName())] < 300) {
                    if (!isset($this->plugin->jetpackChargeTick[strtolower($player->getName())])) {
                        $this->plugin->jetpackChargeTick[strtolower($player->getName())] = 0;
                    }
                    $this->plugin->jetpackChargeTick[strtolower($player->getName())]++;
                    if ($this->plugin->jetpackChargeTick[strtolower($player->getName())] >= 30) {
                        $this->plugin->flyremaining[strtolower($player->getName())]++;
                    }
                }
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