<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class RecursiveEnchant extends ReactiveEnchantment
{
    /** @var array */
    public static $isUsing;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if (isset(self::$isUsing[$player->getName()])) return;
        self::$isUsing[$player->getName()] = true;
        $this->safeReact($player, $item, $inventory, $slot, $event, $level, $stack);
        unset(self::$isUsing[$player->getName()]);
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
    }
}