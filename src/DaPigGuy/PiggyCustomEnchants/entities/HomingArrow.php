<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class HomingArrow extends Arrow
{
    private int $enchantmentLevel;

    public function __construct(Location $location, ?Entity $shootingEntity, bool $critical, ?CompoundTag $nbt = null, int $enchantmentLevel = 1)
    {
        parent::__construct($location, $shootingEntity, $critical, $nbt);
        $this->enchantmentLevel = $enchantmentLevel;
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if (!$this->closed && !$this->isFlaggedForDespawn() && $this->blockHit === null) {
            $target = $this->findNearestEntity($this->enchantmentLevel * 10);
            if ($target !== null) {
                $this->setMotion($target->getPosition()->add(0, $target->size->getHeight() / 2, 0)->subtractVector($this->getPosition())->normalize()->multiply(1.5));
                $this->lookAt($target->getPosition()->add(0, $target->size->getHeight() / 2, 0));
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
        foreach ($this->getWorld()->getEntities() as $entity) {
            $distance = $this->location->distance($entity->getPosition());
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
        $horizontal = sqrt(($target->x - $this->location->x) ** 2 + ($target->z - $this->location->z) ** 2);
        $vertical = $target->y - $this->location->y;
        $this->location->pitch = -atan2($vertical, $horizontal) / M_PI * 180;

        $xDist = $target->x - $this->location->x;
        $zDist = $target->z - $this->location->z;
        $this->location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
        if ($this->location->yaw < 0) {
            $this->location->yaw += 360.0;
        }
    }
}