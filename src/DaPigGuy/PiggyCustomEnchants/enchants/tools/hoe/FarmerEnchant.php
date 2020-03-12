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
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class FarmerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Farmer";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_HOE;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $block = $event->getBlock();
            if ($block instanceof Crops) {
                $seed = $block->getPickedItem();
                if ($player->getInventory()->contains($seed)) {
                    $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $seed, $block): void {
                        $block->getLevel()->useItemOn($block->subtract(0, 1), $seed, Vector3::SIDE_UP, $block->subtract(0, 1), $player);
                        $player->getInventory()->removeItem($seed->setCount(1));
                    }), 1);
                }
            }
        }
    }
}