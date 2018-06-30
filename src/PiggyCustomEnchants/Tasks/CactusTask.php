<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\scheduler\Task;

/**
 * Class CactusTask
 * @package PiggyCustomEnchants\Tasks
 */
class CactusTask extends Task
{
    /** @var Main */
    private $plugin;

    /**
     * CactusTask constructor.
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
            foreach ($player->getArmorInventory()->getContents() as $item) {
                if ($item->getEnchantment(CustomEnchantsIds::CACTUS) !== null) {
                    foreach ($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(1, 0, 1), $player) as $p) {
                        $ev = new EntityDamageByEntityEvent($player, $p, EntityDamageEvent::CAUSE_CONTACT, 1);
                        $p->attack($ev);
                    }
                    break;
                }
            }
        }
    }
}