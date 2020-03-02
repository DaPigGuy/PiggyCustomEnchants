<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class AntitoxinEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Antitoxin";
    /** @var int */
    public $maxLevel = 1;

    public function getReagent(): array
    {
        return [EntityEffectAddEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityEffectAddEvent) {
            if ($event->getEffect()->getType() === VanillaEffects::POISON()) $event->setCancelled();
        }
    }

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_HELMET;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_HELMET;
    }
}