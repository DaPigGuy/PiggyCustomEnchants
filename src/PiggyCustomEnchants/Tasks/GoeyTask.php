<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\Main;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

/**
 * Class GoeyTask
 * @package PiggyCustomEnchants
 */
class GoeyTask extends Task
{
    /** @var Main */
    private $plugin;
    /** @var Entity */
    private $entity;
    /** @var int */
    private $level;

    /**
     * GoeyTask constructor.
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
        $this->entity->setMotion(new Vector3($this->entity->getMotion()->x, (3 * $this->level * 0.05) + 0.75, $this->entity->getMotion()->z));
    }
}