<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class FertilizerEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe
 */
class FertilizerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Fertilizer";
    /** @var int */
    public $maxLevel = 3;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerInteractEvent::class];
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
        if ($event instanceof PlayerInteractEvent) {
            $block = $event->getBlock();
            if ($block->getId() === Block::GRASS || ($block->getId() === Block::DIRT && $block->getDamage() === 0)) {
                for ($x = -$level; $x <= $level; $x++) {
                    for ($z = -$level; $z <= $level; $z++) {
                        $newBlock = $block->getLevel()->getBlock($block->add($x, 0, $z));
                        if ($newBlock->getId() === Block::GRASS || ($newBlock->getId() === Block::DIRT && $newBlock->getDamage() === 0)) {
                            $this->setCooldown($player, 1);
                            $block->getLevel()->useItemOn($newBlock, $item, 0, $newBlock, $player);
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