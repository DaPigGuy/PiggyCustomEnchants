<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class DisarmorEnchant extends DisarmingEnchant
{
    /** @var string */
    public $name = "Disarmor";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Player) {
                if (count($armorContents = $entity->getArmorInventory()->getContents(false)) > 0) {
                    $item = $armorContents[array_rand($armorContents)];
                    $entity->getArmorInventory()->removeItem($item);
                    $entity->dropItem($item);
                }
            }
        }
    }
}