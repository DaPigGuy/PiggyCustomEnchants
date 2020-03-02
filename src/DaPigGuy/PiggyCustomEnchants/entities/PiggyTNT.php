<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\PiggyExplosion;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\player\Player;
use pocketmine\world\Position;

class PiggyTNT extends PrimedTNT
{
    /** @var bool */
    public $worldDamage = true;

    public function explode(): void
    {
        $ownerEntity = $this->getOwningEntity();
        if ($ownerEntity === null || !$ownerEntity instanceof Player) {
            parent::explode();
            return;
        }
        $ev = new ExplosionPrimeEvent($this, 4);
        $ev->setBlockBreaking($this->worldDamage);
        $ev->call();
        if (!$ev->isCancelled()) {
            $explosion = new PiggyExplosion(Position::fromObject($this->location->add(0, $this->height / 2, 0), $this->location->world), $ev->getForce(), $ownerEntity);
            if ($ev->isBlockBreaking()) {
                $explosion->explodeA();
            }
            $explosion->explodeB();
        }
    }
}