<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\block\BlockTypeIds;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\player\Player;

class FertilizerEnchant extends RecursiveEnchant
{
    public string $name = "Fertilizer";
    public int $rarity = Rarity::UNCOMMON;
    public int $maxLevel = 3;

    public int $itemType = CustomEnchant::ITEM_TYPE_HOE;

    public function getReagent(): array
    {
        return [PlayerInteractEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 1];
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerInteractEvent) {
            $block = $event->getBlock();
            if ($block->getTypeId() === BlockTypeIds::GRASS || ($block->getTypeId() === BlockTypeIds::DIRT && $block->getStateId() === 0)) {
                $radius = $level * $this->extraData["radiusMultiplier"];
                for ($x = -$radius; $x <= $radius; $x++) {
                    for ($z = -$radius; $z <= $radius; $z++) {
                        $newBlock = $block->getPosition()->getWorld()->getBlock($block->getPosition()->add($x, 0, $z));
                        if ($newBlock->getTypeId() === BlockTypeIds::GRASS || ($newBlock->getTypeId() === BlockTypeIds::DIRT && $newBlock->getStateId() === 0)) {
                            $block->getPosition()->getWorld()->useItemOn($newBlock->getPosition(), $item, Facing::UP, $newBlock->getPosition(), $player);
                        }
                    }
                }
            }
        }
    }
}