<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class HomingArrow extends Arrow
{
    /** @var int */
    private $enchantmentLevel;

    public function __construct(Level $level, CompoundTag $nbt, ?Entity $shootingEntity = null, bool $critical = false, int $enchantmentLevel = 1)
    {
        $this->enchantmentLevel = $enchantmentLevel;
        parent::__construct($level, $nbt, $shootingEntity, $critical);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if (!$this->closed && !$this->isFlaggedForDespawn() && $this->blockHit === null) {
            $target = $this->findNearestEntity($this->enchantmentLevel * 10);
            if ($target !== null) {
                $this->setMotion($target->add(0, $target->height / 2)->subtract($this)->normalize()->multiply(1.5));
                $this->lookAt($target->add(0, $target->height / 2));
            }
        }
        return parent::entityBaseTick($tickDiff);
    }

    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    public function findNearestEntity(int $range): ?Living
    {
        $nearestEntity = null;
        $nearestEntityDistance = $range;
        foreach ($this->getLevel()->getEntities() as $entity) {
            $distance = $this->distance($entity);
            if ($entity instanceof Living && $distance <= $range && $distance < $nearestEntityDistance && ($owner = $this->getOwningEntity()) !== $entity && $entity->isAlive() && !$entity->isClosed() && !$entity->isFlaggedForDespawn()) {
                if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entity)) {
                    $nearestEntity = $entity;
                    $nearestEntityDistance = $distance;
                }
            }
        }
        return $nearestEntity;
    }

    public function lookAt(Vector3 $target): void
    {
        $horizontal = sqrt(($target->x - $this->x) ** 2 + ($target->z - $this->z) ** 2);
        $vertical = $target->y - $this->y;
        $this->pitch = -atan2($vertical, $horizontal) / M_PI * 180;

        $xDist = $target->x - $this->x;
        $zDist = $target->z - $this->z;
        $this->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
        if ($this->yaw < 0) {
            $this->yaw += 360.0;
        }
    }
}