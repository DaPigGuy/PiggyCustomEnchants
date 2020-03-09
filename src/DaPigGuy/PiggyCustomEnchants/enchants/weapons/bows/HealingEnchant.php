<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class HealingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Healing";

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["healthReplenishMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $player->setHealth($player->getHealth() + $event->getFinalDamage() + $level * $this->extraData["healthReplenishMultiplier"] > $player->getMaxHealth() ? $player->getMaxHealth() : $player->getHealth() + $event->getFinalDamage() + $level * $this->extraData["healthReplenishMultiplier"]);
            foreach ($event->getModifiers() as $modifier => $damage) {
                $event->setModifier(0, $modifier);
            }
            $event->setBaseDamage(0);
        }
    }
}