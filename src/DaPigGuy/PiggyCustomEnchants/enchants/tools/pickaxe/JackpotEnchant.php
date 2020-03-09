<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\pickaxe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class JackpotEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Jackpot";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_MYTHIC;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_PICKAXE;

    const ORE_TIERS = [
        Block::COAL_ORE,
        Block::IRON_ORE,
        Block::GOLD_ORE,
        Block::DIAMOND_ORE,
        Block::EMERALD_ORE
    ];

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            /** @var int $key */
            $key = array_search($event->getBlock()->getId(), self::ORE_TIERS);
            if ($key !== false) {
                if (isset(self::ORE_TIERS[$key + 1])) {
                    $drops = $event->getDrops();
                    foreach ($drops as $k => $drop) {
                        if (in_array($drop, $event->getBlock()->getDrops($item))) {
                            unset($drops[$k]);
                        }
                    }
                    $drops = array_merge($drops, Block::get(self::ORE_TIERS[$key + 1])->getDrops(Item::get(Item::DIAMOND_PICKAXE)));
                    $event->setDrops($drops);
                }
            }
        }
    }

    public function getPriority(): int
    {
        return 3;
    }
}
