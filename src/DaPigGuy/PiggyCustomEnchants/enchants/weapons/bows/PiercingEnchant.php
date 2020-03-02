<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class PiercingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Piercing";
    /** @var int */
    public $maxLevel = 1;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $event->setModifier(0, EntityDamageEvent::MODIFIER_ARMOR);
            $event->setModifier(0, EntityDamageEvent::MODIFIER_ARMOR_ENCHANTMENTS);
        }
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOW;
    }
}