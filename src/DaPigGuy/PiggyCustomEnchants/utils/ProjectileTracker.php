<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Item;

/**
 * Class ProjectileTracker
 * @package DaPigGuy\PiggyCustomEnchants\utils
 */
class ProjectileTracker
{
    /** @var Item[] */
    public static $projectile = [];

    /**
     * @param Projectile $projectile
     * @param Item $item
     */
    public static function addProjectile(Projectile $projectile, Item $item): void
    {
        self::$projectile[$projectile->getId()] = $item;
    }

    /**
     * @param Projectile $projectile
     * @return bool
     */
    public static function isTrackedProjectile(Projectile $projectile): bool
    {
        return isset(self::$projectile[$projectile->getId()]);
    }

    /**
     * @param Projectile $projectile
     * @return Item|null
     */
    public static function getItem(Projectile $projectile): ?Item
    {
        if (!isset(self::$projectile[$projectile->getId()])) return null;
        return self::$projectile[$projectile->getId()];
    }

    /**
     * @param Projectile $projectile
     * @return array
     */
    public static function getEnchantments(Projectile $projectile): array
    {
        if (!isset(self::$projectile[$projectile->getId()])) return [];
        $item = self::$projectile[$projectile->getId()];
        return $item->getEnchantments();
    }

    /**
     * @param Projectile $projectile
     */
    public static function removeProjectile(Projectile $projectile): void
    {
        if (!isset(self::$projectile[$projectile->getId()])) return;
        unset(self::$projectile[$projectile->getId()]);
    }
}