<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class SmeltingEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools
 */
class SmeltingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Smelting";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
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
        if ($event instanceof BlockBreakEvent) {
            $event->setDrops(array_map(function (Item $item) {
                $recipe = $this->plugin->getServer()->getCraftingManager()->matchFurnaceRecipe($item);
                if ($recipe !== null) $item = $recipe->getResult();
                return $item;
            }, $event->getDrops()));
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 2;
    }
}