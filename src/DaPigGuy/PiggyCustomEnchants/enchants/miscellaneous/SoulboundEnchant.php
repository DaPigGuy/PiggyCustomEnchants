<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class SoulboundEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Soulbound";

    public function getReagent(): array
    {
        return [PlayerDeathEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerDeathEvent) {
            $drops = $event->getDrops();
            unset($drops[array_search($item, $drops)]);
            $event->setDrops($drops);
            $level > 1 ? $item->addEnchantment($item->getEnchantment(CustomEnchantIds::SOULBOUND)->setLevel($level - 1)) : $item->removeEnchantment(CustomEnchantIds::SOULBOUND);
            if (count($item->getEnchantments()) === 0) $item->removeNamedTagEntry(Item::TAG_ENCH);
            $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($inventory, $slot, $item): void {
                $inventory->setItem($slot, $item);
            }), 1);
        }
    }

    public function getUsageType(): int
    {
        return self::TYPE_ANY_INVENTORY;
    }

    public function getItemType(): int
    {
        return self::ITEM_TYPE_GLOBAL;
    }

}