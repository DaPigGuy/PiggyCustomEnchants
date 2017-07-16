<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\Main;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\scheduler\PluginTask;

/**
 * Class GrapplingTask
 * @package PiggyCustomEnchants
 */
class GrapplingTask extends PluginTask
{
    private $plugin;
    private $location;
    private $entity;

    /**
     * GrapplingTask constructor.
     * @param Main $plugin
     * @param Position $location
     * @param Entity $entity
     */
    public function __construct(Main $plugin, Position $location, Entity $entity)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->location = $location;
        $this->entity = $entity;
    }

    /**
     * @param $currentTick
     */
    public function onRun(int $currentTick)
    {
        $location = $this->location;
        $entityloc = $this->entity->getPosition();
        $g = -0.08;
        $d = $location->distance($entityloc);
        $t = $d;
        $v_x = (1.0 + 0.07 * $t) * ($location->x - $entityloc->x) / $t;
        $v_y = (1.0 + 0.03 * $t) * ($location->y - $entityloc->y) / $t - 0.5 * $g * $t;
        $v_z = (1.0 + 0.07 * $t) * ($location->z - $entityloc->z) / $t;
        $v = $this->entity->getMotion();
        $v->setComponents($v_x, $v_y, $v_z);
        $this->entity->setMotion($v);
    }
}