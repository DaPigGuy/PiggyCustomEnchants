<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\traits\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Trait BlockBreakingTrait
 * @package DaPigGuy\PiggyCustomEnchants\enchants\traits\tools
 */
trait BlockBreakingTrait
{
    /** @var array */
    public $isBreaking;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
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
        if (isset($this->isBreaking[$player->getName()])) return;
        $this->isBreaking[$player->getName()] = true;
        $this->breakBlocks($player, $item, $inventory, $slot, $event, $level, $stack);
        unset($this->isBreaking[$player->getName()]);
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
    public function breakBlocks(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
    }
}