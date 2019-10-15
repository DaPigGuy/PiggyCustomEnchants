<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class DisarmorEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class DisarmorEnchant extends DisarmingEnchant
{
    /** @var string */
    public $name = "Disarmor";

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
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