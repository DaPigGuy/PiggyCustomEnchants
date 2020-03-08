<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Utils as PMMPUtils;

class AllyChecks
{
    /** @var array */
    private static $checks = [];

    /**
     * @param Plugin $plugin
     * @param callable $check
     * @phpstan-param callable(Player $player, Entity $entity): bool $check
     */
    public static function addCheck(Plugin $plugin, callable $check): void
    {
        PMMPUtils::validateCallableSignature(function (Player $player, Entity $entity): bool {
            return true;
        }, $check);
        self::$checks[] = [$plugin, $check];
    }

    public static function isAlly(Player $player, Entity $entity): bool
    {
        foreach (self::$checks as $check) {
            if ($check[0]->isEnabled()) {
                if (($check[1])($player, $entity)) return true;
            }
        }
        return false;
    }
}