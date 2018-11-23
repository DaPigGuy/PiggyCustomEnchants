<?php

namespace PiggyCustomEnchants\Entities;


use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

/**
 * Class PiggyProjectile
 * @package PiggyCustomEnchants\Entities
 */
class PiggyProjectile extends Projectile
{
    /** @var bool */
    public $placeholder;
    /** @var Position */
    private $ownerOriginalLocation;

    /**
     * Used to replace const NETWORK_ID to resolve registration conflicts with vanilla entities
     * @var int
     */
    const TYPE_ID = 0;

    /**
     * PiggyProjectile constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param Entity|null $shootingEntity
     * @param bool $placeholder
     */
    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, $placeholder = false)
    {
        $this->placeholder = $placeholder;
        $this->ownerOriginalLocation = $shootingEntity->getPosition();
        parent::__construct($level, $nbt, $shootingEntity);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isCollided) {
            if ($this->getOwningEntity() instanceof Player && $this->placeholder) {
                $this->getOwningEntity()->sendPosition($this->add($this->getDirectionVector()->multiply(2)), $this->yaw * 2 <= 360 ? $this->yaw * 2 : $this->yaw / 2, $this->pitch);
            }
        } else {
            if ($this->placeholder) {
                $this->getOwningEntity()->teleport($this->ownerOriginalLocation);
            }
            $this->flagForDespawn();
            $hasUpdate = true;
        }
        return $hasUpdate;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player): void
    {
        parent::spawnTo($player);
        $pk = new AddEntityPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->type = static::TYPE_ID;
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->propertyManager->getAll();
        $player->dataPacket($pk);
    }
}