<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class AutoRepairEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous
 */
class AutoRepairEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Autorepair";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($item->getDamage() === 0) return;
        $newDir = $item->getDamage() - (1 + (1 * $level));
        if ($newDir < 0) {
            $item->setDamage(0);
        } else {
            $item->setDamage($newDir);
        }
        $inventory->setItem($slot, $item);
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ANY_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_DAMAGEABLE;
    }
}