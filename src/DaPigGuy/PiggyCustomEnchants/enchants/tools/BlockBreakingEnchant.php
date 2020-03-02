<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class BlockBreakingEnchant extends ReactiveEnchantment
{
    /** @var array */
    public static $isBreaking;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if (isset(self::$isBreaking[$player->getName()])) return;
        self::$isBreaking[$player->getName()] = true;
        $this->breakBlocks($player, $item, $inventory, $slot, $event, $level, $stack);
        unset(self::$isBreaking[$player->getName()]);
    }

    public function breakBlocks(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
    }
}