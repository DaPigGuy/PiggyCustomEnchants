<?php

namespace DaPigGuy\PiggyCustomEnchants\Entities;

use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\Player;

/**
 * Class PiggyLightning
 * @package DaPigGuy\PiggyCustomEnchants\Entities
 */
class PiggyLightning extends Entity
{
    /**
     * Used to replace const NETWORK_ID to resolve registration conflicts with vanilla entities
     * @var int
     */
    const TYPE_ID = 93;
    /** @var float */
    public $width = 0.3;
    /** @var float */
    public $length = 0.9;
    /** @var float */
    public $height = 1.8;

    protected $age = 0;

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $this->age += $tickDiff;
        $hasUpdate = parent::entityBaseTick($tickDiff);
        foreach ($this->getLevel()->getNearbyEntities($this->getBoundingBox()->expandedCopy(4, 3, 4), $this) as $entity) {
            if ($entity instanceof Living && $entity->isAlive() && $this->getOwningEntityId() !== $entity->getId()) {
                $ev = new EntityCombustByEntityEvent($this, $entity, mt_rand(3, 8));
                $ev->call();
                if (!$ev->isCancelled()) {
                    $entity->setOnFire($ev->getDuration());
                }
                $ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_CUSTOM, 5);
                $ev->call();
                if (!$ev->isCancelled()) {
                    $entity->attack($ev);
                }
            }
        }
        if ($this->getLevel()->getBlock($this)->canBeFlowedInto() && Main::$lightningFlames) {
            $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
        }
        if ($this->age > 20) {
            $this->flagForDespawn();
        }
        return $hasUpdate;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player): void
    {
        $pk = new AddActorPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->type = static::TYPE_ID;
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->propertyManager->getAll();
        $player->sendDataPacket($pk);
    }
}