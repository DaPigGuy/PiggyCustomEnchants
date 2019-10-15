<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\entity\object\ItemEntity;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class VacuumEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate
 */
class VacuumEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Vacuum";
    /** @var int */
    public $maxLevel = 3;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        foreach ($player->getLevel()->getEntities() as $entity) {
            if ($entity instanceof ItemEntity) {
                $distance = $player->distance($entity);
                if ($distance <= 3 * $level) {
                    $entity->setMotion($player->subtract($entity)->divide(3 * $level)->multiply($level));
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return self::TYPE_CHESTPLATE;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return self::ITEM_TYPE_CHESTPLATE;
    }
}