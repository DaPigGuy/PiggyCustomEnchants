<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class PiggyWitherSkull extends PiggyProjectile
{
    protected float $damage = 0;

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        if ($entityHit instanceof Living) {
            $owner = $this->getOwningEntity();
            if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entityHit)) {
                $effect = new EffectInstance(VanillaEffects::WITHER(), 800, 1);
                $entityHit->getEffects()->add($effect);
            }
        }
        parent::onHitEntity($entityHit, $hitResult);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::WITHER_SKULL;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.5, 0.5);
    }
}