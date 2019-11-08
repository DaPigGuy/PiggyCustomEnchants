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

/**
 * Class FarmerEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe
 */
class FarmerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Farmer";
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

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_HOE;
    }
}