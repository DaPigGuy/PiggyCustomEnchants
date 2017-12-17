<?php

namespace PiggyCustomEnchants\Entities;

use PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;

/**
 * Class Fireball
 * @package PiggyCustomEnchants\Entities
 */
class Fireball extends Projectile
{
    const NETWORK_ID = 94;

    protected $damage = 5;

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
                } else {
                    $this->flagForDespawn();
                }
            }
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        return $hasUpdate;
    }
}