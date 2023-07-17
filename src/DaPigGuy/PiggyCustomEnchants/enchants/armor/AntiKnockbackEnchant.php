<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class AntiKnockbackEnchant extends ReactiveEnchantment
{
    public string $name = "Anti Knockback";
    public int $maxLevel = 1;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $stack = min($stack, 4);
            $event->setKnockBack($event->getKnockBack() * (4 - $stack) / (5 - $stack));
        }
    }
}