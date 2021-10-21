<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\block\Crops;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class HarvestEnchant extends RecursiveEnchant
{
    public string $name = "Harvest";
    public int $rarity = Rarity::UNCOMMON;
    public int $maxLevel = 3;

    public int $itemType = CustomEnchant::ITEM_TYPE_HOE;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 1];
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $block = $event->getBlock();
            if ($block instanceof Crops) {
                $radius = $level * $this->extraData["radiusMultiplier"];
                for ($x = -$radius; $x <= $radius; $x++) {
                    for ($z = -$radius; $z <= $radius; $z++) {
                        if ($block->getPosition()->getWorld()->getBlock($block->getPosition()->add($x, 0, $z)) instanceof Crops) {
                            $block->getPosition()->getWorld()->useBreakOn($block->getPosition()->add($x, 0, $z), $item, $player);
                        }
                    }
                }
            }
        }
    }
}