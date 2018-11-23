<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;

/**
 * Class PiggyWitherSkull
 * @package PiggyCustomEnchants\Entities
 */
class PiggyWitherSkull extends PiggyProjectile
{
    /** @var float */
    public $width = 0.5;
    /** @var float */
    public $length = 0.5;
    /** @var float */
    public $height = 0.5;

    /** @var float */
    protected $drag = 0.01;
    /** @var float */
    protected $gravity = 0.05;

    /** @var int */
    protected $damage = 0;

    /**
     * Used to replace const NETWORK_ID to resolve registration conflicts with vanilla entities
     * @var int
     */
    const TYPE_ID = 89;

    /**
     * @param Entity $entity
     */
    public function onCollideWithEntity(Entity $entity)
    {
        if ($entity instanceof Living) {
            $effect = new EffectInstance(Effect::getEffect(Effect::WITHER), 800, 1);
            $entity->addEffect($effect);
        }
        parent::onCollideWithEntity($entity);
    }
}