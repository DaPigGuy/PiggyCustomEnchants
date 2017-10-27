<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\block\Block;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\MovingObjectPosition;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

/**
 * Class Fireball
 * @package PiggyCustomEnchants\Entities
 */
class Fireball extends Projectile
{
    const NETWORK_ID = 94;

    /**
     * @param $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick) : bool
    {
        if ($this->closed) {
            return false;
        }


        $tickDiff = $currentTick - $this->lastUpdate;
        if ($tickDiff <= 0 and !$this->justCreated) {
            return true;
        }
        $this->lastUpdate = $currentTick;

        $hasUpdate = $this->entityBaseTick($tickDiff);

        if ($this->isAlive()) {

            $movingObjectPosition = null;

            if (!$this->isCollided) {
                $this->motionY -= $this->gravity;
            }

            $moveVector = new Vector3($this->x + $this->motionX, $this->y + $this->motionY, $this->z + $this->motionZ);

            $list = $this->getLevel()->getCollidingEntities($this->boundingBox->addCoord($this->motionX, $this->motionY, $this->motionZ)->expand(1, 1, 1), $this);

            $nearDistance = PHP_INT_MAX;
            $nearEntity = null;

            foreach ($list as $entity) {
                if (/*!$entity->canCollideWith($this) or */
                ($entity === $this->getOwningEntity() and $this->ticksLived < 5)
                ) {
                    continue;
                }

                $axisalignedbb = $entity->boundingBox->grow(0.3, 0.3, 0.3);
                $ob = $axisalignedbb->calculateIntercept($this, $moveVector);

                if ($ob === null) {
                    continue;
                }

                $distance = $this->distanceSquared($ob->hitVector);

                if ($distance < $nearDistance) {
                    $nearDistance = $distance;
                    $nearEntity = $entity;
                }
            }

            if ($nearEntity !== null) {
                $movingObjectPosition = MovingObjectPosition::fromEntity($nearEntity);
            }

            if ($movingObjectPosition !== null) {
                if ($movingObjectPosition->entityHit !== null) {

                    $this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));

                    $damage = 5;

                    if ($this->getOwningEntity() === null) {
                        $ev = new EntityDamageByEntityEvent($this, $movingObjectPosition->entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
                    } else {
                        $ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $movingObjectPosition->entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
                    }

                    $movingObjectPosition->entityHit->attack($ev);

                    $this->hadCollision = true;

                    $ev = new EntityCombustByEntityEvent($this, $movingObjectPosition->entityHit, 5);
                    $this->server->getPluginManager()->callEvent($ev);
                    if (!$ev->isCancelled()) {
                        $movingObjectPosition->entityHit->setOnFire(5);
                    }

                    $this->kill();
                    return true;
                }
            }

            $this->move($this->motionX, $this->motionY, $this->motionZ);

            if ($this->isCollided and !$this->hadCollision) {
                $this->hadCollision = true;

                $this->motionX = 0;
                $this->motionY = 0;
                $this->motionZ = 0;

                $this->server->getPluginManager()->callEvent(new ProjectileHitEvent($this));
                if ($this->onGround) {
                    if ($this->getLevel()->getBlock($this)->canBeFlowedInto()) {
                        $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
                    }
                }
                $this->kill();
            }

            if (!$this->onGround or abs($this->motionX) > 0.00001 or abs($this->motionY) > 0.00001 or abs($this->motionZ) > 0.00001) {
                $f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
                $this->yaw = (atan2($this->motionX, $this->motionZ) * 180 / M_PI);
                $this->pitch = (atan2($this->motionY, $f) * 180 / M_PI);
                $hasUpdate = true;
            }

            $this->updateMovement();

        }

        return $hasUpdate;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        $pk->type = Fireball::NETWORK_ID;
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

}