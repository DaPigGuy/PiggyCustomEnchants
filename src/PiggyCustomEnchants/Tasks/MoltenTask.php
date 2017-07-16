<?php

namespace PiggyCustomEnchants\Tasks;


use PiggyCustomEnchants\Main;
use pocketmine\entity\Entity;
use pocketmine\scheduler\PluginTask;

/**
 * Class MoltenTask
 * @package PiggyCustomEnchants\Tasks
 */
class MoltenTask extends PluginTask
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
        parent::__construct($plugin);
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