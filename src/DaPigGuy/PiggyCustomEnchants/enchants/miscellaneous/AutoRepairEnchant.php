<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\player\Player;

class AutoRepairEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Autorepair";

    public function getReagent(): array
    {
        return [PlayerMoveEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["baseRepair" => 1, "repairMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if (!$item instanceof Durable || $item->getMeta() === 0) return;
        $newDir = $item->getMeta() - ((int)$this->extraData["baseRepair"] + ((int)$this->extraData["repairMultiplier"] * $level));
        if ($newDir < 0) {
            $item->setDamage(0);
        } else {
            $item->setDamage($newDir);
        }
        $inventory->setItem($slot, $item);
    }

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ANY_INVENTORY;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_DAMAGEABLE;
    }
}