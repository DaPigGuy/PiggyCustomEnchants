<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\entity\Item;
use pocketmine\scheduler\PluginTask;

/**
 * Class VacuumTask
 * @package PiggyCustomEnchants\Tasks
 */
class VacuumTask extends PluginTask
{
    private $plugin;

    /**
     * VacuumTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getChestplate(), CustomEnchants::VACUUM);
            if ($enchantment !== null) {
                foreach ($player->getLevel()->getEntities() as $entity) {
                    if ($entity instanceof Item) {
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