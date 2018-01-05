<?php

namespace PiggyCustomEnchants\Entities;


use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\Explosion;

/**
 * Class WitherSkull
 * @package PiggyCustomEnchants\Entities
 */
class WitherSkull extends Projectile
{
    public $width = 0.5;
    public $length = 0.5;
    public $height = 0.5;

    protected $drag = 0.01;
    protected $gravity = 0.05;

    protected $damage = 0;

    const NETWORK_ID = 89;

    /**
     * @param Entity $entity
     */
    public function onCollideWithEntity(Entity $entity)
    {
        if ($entity instanceof Living) {
            $effect = Effect::getEffect(Effect::WITHER);
            $effect->setAmplifier(1);
            $effect->setDuration(800);
            $entity->addEffect($effect);
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
                    $this->server->getPluginManager()->callEvent($ev = new ProjectileHitEvent($this));
                    //TODO: Add explosion
                } else {
                    $this->flagForDespawn();
                }
            }
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        return $hasUpdate;
    }
}