<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\RayTraceResult;

/**
 * Class PiggyWitherSkull
 * @package DaPigGuy\PiggyCustomEnchants\entities
 */
class PiggyWitherSkull extends PiggyProjectile
{
    const NETWORK_ID = Entity::WITHER_SKULL;

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
     * @param Entity $entityHit
     * @param RayTraceResult $hitResult
     */
    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        if ($entityHit instanceof Living) {
            $effect = new EffectInstance(Effect::getEffect(Effect::WITHER), 800, 1);
            $entityHit->addEffect($effect);
        }
        parent::onHitEntity($entityHit, $hitResult);
    }
}