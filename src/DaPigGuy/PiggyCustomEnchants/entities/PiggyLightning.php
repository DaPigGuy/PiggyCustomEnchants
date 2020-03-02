<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;

class PiggyLightning extends Entity
{
    const NETWORK_ID = EntityIds::LIGHTNING_BOLT;

    /** @var float */
    public $width = 0.3;
    /** @var float */
    public $length = 0.9;
    /** @var float */
    public $height = 1.8;

    /** @var int */
    protected $age = 0;

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $this->age += $tickDiff;
        $hasUpdate = parent::entityBaseTick($tickDiff);
        foreach ($this->getLevel()->getNearbyEntities($this->getBoundingBox()->expandedCopy(4, 3, 4), $this) as $entity) {
            if ($entity instanceof Living && $entity->isAlive() && $this->getOwningEntity() !== $entity) {
                $owner = $this->getOwningEntity();
                if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entity)) {
                    $ev = new EntityCombustByEntityEvent($this, $entity, mt_rand(3, 8));
                    $ev->call();
                    if (!$ev->isCancelled()) {
                        $entity->setOnFire($ev->getDuration());
                    }
                }
                $ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_CUSTOM, 5);
                $ev->call();
                if (!$ev->isCancelled()) {
                    $entity->attack($ev);
                }
            }
        }
        if ($this->getLevel()->getBlock($this)->canBeFlowedInto() && CustomEnchantManager::getPlugin()->getConfig()->getNested("world-damage.lightning", false)) {
            $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
        }
        if ($this->age > 20) {
            $this->flagForDespawn();
        }
        return $hasUpdate;
    }
}