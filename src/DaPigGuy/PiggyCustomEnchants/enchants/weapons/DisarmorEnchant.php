<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class DisarmorEnchant extends DisarmingEnchant
{
    public string $name = "Disarmor";
    public int $rarity = Rarity::UNCOMMON;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Player) {
                if (count($armorContents = $entity->getArmorInventory()->getContents()) > 0) {
                    $item = $armorContents[array_rand($armorContents)];
                    $entity->getArmorInventory()->removeItem($item);
                    $entity->dropItem($item);
                }
            }
        }
    }
}