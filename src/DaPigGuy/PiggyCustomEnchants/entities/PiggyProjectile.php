<?php

namespace DaPigGuy\PiggyCustomEnchants\entities;

use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileHitEvent;

class PiggyProjectile extends Projectile
{
    public function onHit(ProjectileHitEvent $event): void
    {
        $this->flagForDespawn();
        parent::onHit($event);
    }

    public function canSaveWithChunk(): bool
    {
        return false;
    }
}