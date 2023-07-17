<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\PiggyExplosion;
use pocketmine\entity\Location;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\entity\EntityPreExplodeEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\Position;

class PiggyTNT extends PrimedTNT
{
    private bool $worldDamage;

    public function __construct(Location $location, ?CompoundTag $nbt = null, bool $worldDamage = false)
    {
        parent::__construct($location, $nbt);
        $this->worldDamage = $worldDamage;
    }

    public function explode(): void
    {
        $ownerEntity = $this->getOwningEntity();
        if (!$ownerEntity instanceof Player) {
            return;
        }
        $ev = new EntityPreExplodeEvent($this, 4);
        $ev->setBlockBreaking($this->worldDamage);
        $ev->call();
        if (!$ev->isCancelled()) {
            $explosion = new PiggyExplosion(Position::fromObject($this->location->add(0, $this->size->getHeight() / 2, 0), $this->location->world), $ev->getRadius(), $ownerEntity);
            if ($ev->isBlockBreaking()) $explosion->explodeA();
            $explosion->explodeB();
        }
    }
}