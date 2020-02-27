<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World;

/**
 * Class HomingArrow
 * @package DaPigGuy\PiggyCustomEnchants\entities
 */
class HomingArrow extends Arrow
{
    /** @var int */
    private $enchantmentLevel;

    /**
     * HomingArrow constructor.
     * @param World $level
     * @param CompoundTag $nbt
     * @param Entity|null $shootingEntity
     * @param int $enchantmentLevel
     * @param bool $critical
     */
    public function __construct(World $level, CompoundTag $nbt, ?Entity $shootingEntity = null, bool $critical = false, int $enchantmentLevel = 1)
    {
        $this->enchantmentLevel = $enchantmentLevel;
        parent::__construct($level, $nbt, $shootingEntity, $critical);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if (!$this->closed && !$this->isFlaggedForDespawn() && $this->blockHit === null) {
            $target = $this->findNearestEntity($this->enchantmentLevel * 10);
            if ($target !== null) {
                $this->setMotion($target->getPosition()->add(0, $target->height / 2)->subtract($this->getPosition())->normalize()->multiply(1.5));
                $this->lookAt($target->getPosition()->add(0, $target->height / 2));
            }
        }
        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @return int
     */
    public function getEnchantmentLevel(): int
    {
        return $this->enchantmentLevel;
    }

    /**
     * @param int $range
     * @return Living|null
     */
    public function findNearestEntity(int $range): ?Living
    {
        $nearestEntity = null;
        $nearestEntityDistance = $range;
        foreach ($this->getWorld()->getEntities() as $entity) {
            $distance = $this->location->distance($entity);
            if ($entity instanceof Living && $distance <= $range && $distance < $nearestEntityDistance && ($owner = $this->getOwningEntity()) !== $entity && $entity->isAlive() && !$entity->isClosed() && !$entity->isFlaggedForDespawn()) {
                if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entity)) {
                    $nearestEntity = $entity;
                    $nearestEntityDistance = $distance;
                }
            }
        }
        return $nearestEntity;
    }

    /**
     * @param Vector3 $target
     */
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