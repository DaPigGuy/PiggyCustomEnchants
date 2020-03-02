<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class TelepathyEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Telepathy";
    /** @var int */
    public $maxLevel = 1;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $player->getInventory()->addItem(...$event->getDrops());
            $player->getXpManager()->addXp($event->getXpDropAmount());
            $event->setDrops([]);
            $event->setXpDropAmount(0);
        }
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
    }
}