<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\crafting\FurnaceType;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class SmeltingEnchant extends ReactiveEnchantment
{
    public string $name = "Smelting";
    public int $rarity = Rarity::UNCOMMON;
    public int $maxLevel = 1;

    public int $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $event->setDrops(array_map(fn(Item $item) => $this->plugin->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::FURNACE())->match($item)?->getResult() ?? $item, $event->getDrops()));
        }
    }

    public function getPriority(): int
    {
        return 2;
    }
}