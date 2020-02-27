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
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

/**
 * Class JackpotEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools\pickaxe
 */
class JackpotEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Jackpot";

    const ORE_TIERS = [
        BlockLegacyIds::COAL_ORE,
        BlockLegacyIds::IRON_ORE,
        BlockLegacyIds::GOLD_ORE,
        BlockLegacyIds::DIAMOND_ORE,
        BlockLegacyIds::EMERALD_ORE
    ];

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
                    $drops = array_merge($drops, BlockFactory::get(self::ORE_TIERS[$key + 1])->getDrops(VanillaItems::DIAMOND_PICKAXE()));
                    $event->setDrops($drops);
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_PICKAXE;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 3;
    }
}
