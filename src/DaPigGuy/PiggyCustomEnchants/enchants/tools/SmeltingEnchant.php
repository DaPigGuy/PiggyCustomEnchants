<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class SmeltingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Smelting";
    /** @var int */
    public $rarity = Rarity::UNCOMMON;
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $event->setDrops(array_map(function (Item $item) {
                $recipe = $this->plugin->getServer()->getCraftingManager()->getFurnaceRecipeManager()->match($item);
                if ($recipe !== null) $item = $recipe->getResult();
                return $item;
            }, $event->getDrops()));
        }
    }

    public function getPriority(): int
    {
        return 2;
    }
}