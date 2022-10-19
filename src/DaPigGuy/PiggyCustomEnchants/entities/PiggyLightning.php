<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class PiggyLightning extends Entity
{
    protected int $age = 0;

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) return false;
        $this->age += $tickDiff;
        $world = $this->getWorld();
        foreach ($world->getNearbyEntities($this->getBoundingBox()->expandedCopy(4, 3, 4), $this) as $entity) {
            if ($entity instanceof Living && $entity->isAlive() && $this->getOwningEntity() !== $entity) {
                $owner = $this->getOwningEntity();
                if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entity)) {
                    $ev = new EntityCombustByEntityEvent($this, $entity, mt_rand(3, 8));
                    $ev->call();
                    if (!$ev->isCancelled()) $entity->setOnFire($ev->getDuration());
                }
                $ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_CUSTOM, 5);
                $ev->call();
                if (!$ev->isCancelled()) $entity->attack($ev);
            }
        }
        if ($this->getWorld()->getBlock($this->location)->canBeFlowedInto() && CustomEnchantManager::getPlugin()->getConfig()->getNested("world-damage.lightning", false) === true) {
            if ($this->getWorld()->getMaxY() > $this->location->getY() && $this->getWorld()->getMinY() < $this->location->getY()) {
                $this->getWorld()->setBlock($this->location, VanillaBlocks::FIRE());
            } // otherwise OutOfBounds!
        }
        if ($this->age > 20) $this->flagForDespawn();
        return parent::entityBaseTick($tickDiff);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::LIGHTNING_BOLT;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.3);
    }
}