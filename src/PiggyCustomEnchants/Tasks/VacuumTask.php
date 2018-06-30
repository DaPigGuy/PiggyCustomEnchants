<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\entity\object\ItemEntity;
use pocketmine\scheduler\Task;

/**
 * Class VacuumTask
 * @package PiggyCustomEnchants\Tasks
 */
class VacuumTask extends Task
{
    /** @var Main */
    private $plugin;

    /**
     * VacuumTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::VACUUM);
            if ($enchantment !== null) {
                foreach ($player->getLevel()->getEntities() as $entity) {
                    if ($entity instanceof ItemEntity) {
                        $distance = $player->distance($entity);
                        if ($distance <= 3 * $enchantment->getLevel()) {
                            $entity->setMotion($player->subtract($entity)->divide(3 * $enchantment->getLevel())->multiply($enchantment->getLevel()));
                        }
                    }
                }
            }
        }
    }
}