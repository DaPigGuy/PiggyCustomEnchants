<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\axes;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class LumberjackEnchant extends RecursiveEnchant
{
    public string $name = "Lumberjack";
    public int $maxLevel = 1;

    public int $itemType = CustomEnchant::ITEM_TYPE_AXE;

    private const LOG_TYPES = [BlockTypeIds::ACACIA_LOG, BlockTypeIds::BIRCH_LOG, BlockTypeIds::CHERRY_LOG, BlockTypeIds::OAK_LOG, BlockTypeIds::DARK_OAK_LOG, BlockTypeIds::MANGROVE_LOG, BlockTypeIds::JUNGLE_LOG, BlockTypeIds::SPRUCE_LOG];

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["limit" => 800];
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $block = $event->getBlock();
            if ($player->isSneaking()) {
                if (in_array($block->getTypeId(), self::LOG_TYPES)) {
                    $this->breakTree($block, $player);
                }
            }
        }
    }

    public function breakTree(Block $block, Player $player, int $mined = 0): void
    {
        $item = $player->getInventory()->getItemInHand();
        for ($i = 0; $i <= 5; $i++) {
            if ($mined > $this->extraData["limit"]) break;
            $side = $block->getSide($i);
            if (!in_array($side->getTypeId(), self::LOG_TYPES)) continue;
            $player->getWorld()->useBreakOn($side->getPosition(), $item, $player);
            $mined++;
            $this->breakTree($side, $player, $mined);
        }
    }
}