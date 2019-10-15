<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Crops;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class HarvestEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe
 */
class HarvestEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Harvest";
    /** @var int */
    public $maxLevel = 3;

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
            $block = $event->getBlock();
            if ($block instanceof Crops) {
                for ($x = -$level; $x <= $level; $x++) {
                    for ($z = -$level; $z <= $level; $z++) {
                        if ($block->getLevel()->getBlock($block->add($x, 0, $z)) instanceof Crops) {
                            $this->setCooldown($player, 1);
                            $block->getLevel()->useBreakOn($block->add($x, 0, $z), $item, $player);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_HOE;
    }
}