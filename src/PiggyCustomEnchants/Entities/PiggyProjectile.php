<?php

namespace PiggyCustomEnchants\Entities;


use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

/**
 * Class PiggyProjectile
 * @package PiggyCustomEnchants\Entities
 */
class PiggyProjectile extends Projectile
{
    public $placeholder;
    private $ownerOriginalLocation;

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
            $this->getOwningEntity()->teleport($this->ownerOriginalLocation);
            $this->flagForDespawn();
            $hasUpdate = true;
        }
        return $hasUpdate;
    }
}