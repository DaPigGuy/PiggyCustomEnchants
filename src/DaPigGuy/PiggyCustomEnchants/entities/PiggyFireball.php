<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class PiggyFireball extends PiggyProjectile
{
    protected float $damage = 5;

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $owner = $this->getOwningEntity();
        if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entityHit)) {
            $ev = new EntityCombustByEntityEvent($this, $entityHit, 5);
            $ev->call();
            if (!$ev->isCancelled()) $entityHit->setOnFire($ev->getDuration());
        }
        parent::onHitEntity($entityHit, $hitResult);
    }

    public function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        $this->getWorld()->setBlock($this->location, VanillaBlocks::FIRE());
        parent::onHitBlock($blockHit, $hitResult);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::SMALL_FIREBALL;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.5, 0.5);
    }
}