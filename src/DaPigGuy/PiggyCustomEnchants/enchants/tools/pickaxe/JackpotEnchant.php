<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\pickaxe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class JackpotEnchant extends ReactiveEnchantment
{
    public string $name = "Jackpot";
    public int $rarity = Rarity::MYTHIC;

    public int $itemType = CustomEnchant::ITEM_TYPE_PICKAXE;

    const ORE_TIERS = [
        BlockLegacyIds::COAL_ORE,
        BlockLegacyIds::IRON_ORE,
        BlockLegacyIds::GOLD_ORE,
        BlockLegacyIds::DIAMOND_ORE,
        BlockLegacyIds::EMERALD_ORE
    ];

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $key = array_search($event->getBlock()->getId(), self::ORE_TIERS);
            if ($key !== false) {
                if (isset(self::ORE_TIERS[$key + 1])) {
                    $drops = $event->getDrops();
                    foreach ($drops as $k => $drop) {
                        if (in_array($drop, $event->getBlock()->getDrops($item))) {
                            unset($drops[$k]);
                        }
                    }
                    $drops = array_merge($drops, BlockFactory::getInstance()->get(self::ORE_TIERS[$key + 1])->getDrops(VanillaItems::DIAMOND_PICKAXE()));
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
