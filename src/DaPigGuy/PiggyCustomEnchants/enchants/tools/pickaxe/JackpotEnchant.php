<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\pickaxe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
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
        BlockTypeIds::COAL_ORE,
        BlockTypeIds::IRON_ORE,
        BlockTypeIds::GOLD_ORE,
        BlockTypeIds::DIAMOND_ORE,
        BlockTypeIds::EMERALD_ORE
    ];

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $key = array_search($event->getBlock()->getTypeId(), self::ORE_TIERS, true);
            if ($key !== false) {
                if (isset(self::ORE_TIERS[$key + 1])) {
                    $drops = $event->getDrops();
                    foreach ($drops as $k => $drop) {
                        if (in_array($drop, $event->getBlock()->getDrops($item), true)) {
                            unset($drops[$k]);
                        }
                    }
                    $drops = array_merge($drops, $this->getOreDrops(self::ORE_TIERS[$key + 1]));
                    $event->setDrops($drops);
                }
            }
        }
    }

    public function getPriority(): int
    {
        return 3;
    }

    public function getOreDrops(int $tier): array
    {
        $drop = match ($tier) {
            BlockTypeIds::COAL_ORE => VanillaBlocks::COAL_ORE(),
            BlockTypeIds::IRON_ORE => VanillaBlocks::IRON_ORE(),
            BlockTypeIds::GOLD_ORE => VanillaBlocks::GOLD_ORE(),
            BlockTypeIds::DIAMOND_ORE => VanillaBlocks::DIAMOND_ORE(),
            BlockTypeIds::EMERALD_ORE => VanillaBlocks::EMERALD_ORE(),
            default => VanillaBlocks::AIR(),
        };
        return $drop->getDrops(VanillaItems::DIAMOND_PICKAXE());
    }
}
