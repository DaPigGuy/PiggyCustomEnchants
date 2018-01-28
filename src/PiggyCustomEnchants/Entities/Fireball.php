<?php

namespace PiggyCustomEnchants\Entities;

use PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;

/**
 * Class Fireball
 * @package PiggyCustomEnchants\Entities
 */
class Fireball extends PiggyProjectile
{
    protected $drag = 0.01;
    protected $gravity = 0.05;

    protected $damage = 5;

    /**
     * Used to replace const NETWORKD_ID to resolve registration conflicts with vanilla entities
     * @var int
     */
    const TYPE_ID = 94;

    /**
     * @param Entity $entity
     */
    public function onCollideWithEntity(Entity $entity)
    {
        $ev = new EntityCombustByEntityEvent($this, $entity, 5);
        $this->server->getPluginManager()->callEvent($ev);
        if (!$ev->isCancelled()) {
            $entity->setOnFire($ev->getDuration());
        }
        parent::onCollideWithEntity($entity);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        if (!$this->isFlaggedForDespawn()) {
            if ($this->isCollided) {
                if (!$this->hadCollision) {
                    $this->hadCollision = true;
                    $this->motionX = 0;
                    $this->motionY = 0;
                    $this->motionZ = 0;
                    $this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));
                    if (($this->isCollidedHorizontally || $this->isCollidedVertically) && $this->getLevel()->getBlock($this)->canBeFlowedInto() && Main::$blazeFlames) {
                        $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
                    }
                }
                $this->flagForDespawn();

            }
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        return $hasUpdate;
    }
}