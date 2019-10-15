<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector2;
use pocketmine\Player;

/**
 * Class AutoAimEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows
 */
class AutoAimEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Auto Aim";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($player->isSneaking() && $player->isOnGround()) {
            $target = $this->findNearestEntity($player, 50 * $level);
            if ($target !== null) {
                $position = $target->subtract($player);
                $yaw = atan2($position->z, $position->x) * 180 / M_PI - 90;
                $length = (new Vector2($position->x, $position->z))->length();
                $g = 0.006;
                $tmp = 1 - $g * ($g * ($length * $length) + 2 * $position->y);
                $pitch = 180 / M_PI * -(atan((1 - sqrt($tmp)) / ($g * $length)));
                $player->setRotation($yaw, $pitch);
                $player->sendPosition($player);
            }
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOW;
    }

    /**
     * @param Player $player
     * @param int $range
     * @return Living|null
     */
    public function findNearestEntity(Player $player, int $range): ?Living
    {
        $nearestPlayer = null;
        $nearestPlayerDistance = $range;
        foreach ($player->getLevel()->getEntities() as $p) {
            $distance = $player->distance($p);
            if ($p instanceof Living && $distance <= $range && $distance < $nearestPlayerDistance && $player !== $p && $p->isAlive() && !$p->isClosed() && !$p->isFlaggedForDespawn()) {
                $nearestPlayer = $p;
                $nearestPlayerDistance = $distance;
            }
        }
        return $nearestPlayer;
    }
}