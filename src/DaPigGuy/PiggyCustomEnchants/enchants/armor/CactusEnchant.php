<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class CactusEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class CactusEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Cactus";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @return int
     */
    public function getTickingInterval(): int
    {
        return 10;
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        foreach ($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy(1, 0, 1), $player) as $p) {
            $ev = new EntityDamageByEntityEvent($player, $p, EntityDamageEvent::CAUSE_CONTACT, 1);
            $p->attack($ev);
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return self::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return self::ITEM_TYPE_ARMOR;
    }
}