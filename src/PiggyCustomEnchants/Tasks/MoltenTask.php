<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\Main;
use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;

/**
 * Class MoltenTask
 * @package PiggyCustomEnchants\Tasks
 */
class MoltenTask extends Task
{
    private $plugin;
    private $entity;
    private $level;

    /**
     * MoltenTask constructor.
     * @param Main $plugin
     * @param Entity $entity
     * @param $level
     */
    public function __construct(Main $plugin, Entity $entity, $level)
    {
        $this->plugin = $plugin;
        $this->entity = $entity;
        $this->level = $level;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        $this->entity->setOnFire(3 * $this->level);
    }
}