<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\RayTraceResult;
use pocketmine\Player;

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

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        if ($entityHit instanceof Living) {
            $owner = $this->getOwningEntity();
            if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entityHit)) {
                $effect = new EffectInstance(Effect::getEffect(Effect::WITHER), 800, 1);
                $entityHit->addEffect($effect);
            }
        }
        parent::onHitEntity($entityHit, $hitResult);
    }
}