<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\block\Block;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileHitEvent;

/**
 * Class Fireball
 * @package PiggyCustomEnchants\Entities
 */
class Fireball extends Projectile
{
    const NETWORK_ID = 94;

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        if ($this->isAlive()) {
            if ($this->isCollided) {
                if (!$this->hadCollision) {
                    $this->hadCollision = true;
                    $this->motionX = 0;
                    $this->motionY = 0;
                    $this->motionZ = 0;
                    $this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));
                    if ($this->isCollidedHorizontally || $this->isCollidedVertically) {
                        if ($this->getLevel()->getBlock($this)->canBeFlowedInto()) {
                            $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
                        }
                    }
                } else {
                    $this->close();
                }
            }
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        return $hasUpdate;
    }
}