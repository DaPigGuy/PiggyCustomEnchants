<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class TelepathyEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Telepathy";
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            $drops = $event->getDrops();
            foreach ($drops as $key => $drop) {
                if ($player->getInventory()->canAddItem($drop)) {
                    unset($drops[$key]);
                    $player->getInventory()->addItem($drop);
                    continue;
                }
                foreach ($player->getInventory()->all($drop) as $item) {
                    if ($item->getCount() < $item->getMaxStackSize()) {
                        $newDrop = clone $drop->setCount($drop->getCount() - ($item->getMaxStackSize() - $item->getCount()));
                        $player->getInventory()->addItem($drop->setCount($item->getMaxStackSize() - $item->getCount()));
                        $drop = $newDrop;
                    }
                }
                $drops[$key] = $drop;
            }
            $player->addXp($event->getXpDropAmount());
            $event->setDrops($drops);
            $event->setXpDropAmount(0);
        }
    }
}