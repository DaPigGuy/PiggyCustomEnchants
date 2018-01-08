<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

/**
 * Class VolleyArrow
 * @package PiggyCustomEnchants\Entities
 */
class VolleyArrow extends Arrow
{
    private $volley;

    /**
     * VolleyArrow constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param Entity|null $shootingEntity
     * @param bool $critical
     * @param bool $volley
     */
    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false, bool $volley = false)
    {
        $this->volley = $volley;
        parent::__construct($level, $nbt, $shootingEntity, $critical);
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
        if ($this->isVolley()) {
            if (!$this->isFlaggedForDespawn()) {
                if ($this->isCollided) {
                    $this->flagForDespawn();
                    $hasUpdate = true;
                }
            }
        }
        return $hasUpdate;
    }

    /**
     * @return bool
     */
    public function isVolley()
    {
        return $this->volley;
    }
}