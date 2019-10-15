<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\axes;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class LumberjackEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools\axes
 */
class LumberjackEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Lumberjack";
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
            $block = $event->getBlock();
            if ($player->isSneaking()) {
                if ($block->getId() == Block::WOOD || $block->getId() == Block::WOOD2) {
                    $this->breakTree($block, $player);
                }
            }
            $event->setInstaBreak(true);
        }
    }

    /**
     * @param Block $block
     * @param Player $player
     * @param int $mined
     */
    public function breakTree(Block $block, Player $player, int $mined = 0)
    {
        $item = $player->getInventory()->getItemInHand();
        for ($i = 0; $i <= 5; $i++) {
            if ($mined > 800) {
                break;
            }
            $side = $block->getSide($i);
            if ($side->getId() !== Block::WOOD && $side->getId() !== Block::WOOD2) {
                continue;
            }
            $this->setCooldown($player, 1);
            $player->getLevel()->useBreakOn($side, $item, $player);
            $mined++;
            $this->breakTree($side, $player, $mined);
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_AXE;
    }
}