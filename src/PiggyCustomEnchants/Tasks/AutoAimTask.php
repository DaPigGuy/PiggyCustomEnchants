<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class AutoAimTask
 * @package PiggyCustomEnchants
 */
class AutoAimTask extends Task
{
    /** @var Main */
    private $plugin;
    /** @var Vector3[] */
    private $lastPosition;

    /**
     * RadarTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::AUTOAIM);
            if ($enchantment !== null) {
                $detected = $this->plugin->findNearestEntity($player, $enchantment->getLevel() * 50, Player::class, $player);
                if (!is_null($detected)) {
                    if (!isset($this->lastPosition[$player->getLowerCaseName()])) {
                        $this->lastPosition[$player->getLowerCaseName()] = $detected->asVector3();
                    }
                    if ($detected instanceof Player) {
                        if ($detected->asVector3() == $this->lastPosition[$player->getLowerCaseName()] && isset($this->plugin->moved[$player->getLowerCaseName()]) !== true) {
                            break;
                        }
                        if (isset($this->plugin->moved[$player->getLowerCaseName()])) {
                            if ($this->plugin->moved[$player->getLowerCaseName()] < 15) {
                                $this->plugin->moved[$player->getLowerCaseName()]++;
                                break;
                            }
                            unset($this->plugin->moved[$player->getLowerCaseName()]);
                        }
                        $this->lastPosition[$player->getLowerCaseName()] = $detected->asVector3();
                        $player->lookAt($detected);
                        $player->sendPosition($player);
                        break;
                    }
                }
            }
        }
    }
}