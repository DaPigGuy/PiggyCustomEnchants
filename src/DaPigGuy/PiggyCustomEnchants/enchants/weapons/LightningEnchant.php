<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyLightning;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class LightningEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Lightning";
    /** @var int */
    public $rarity = Rarity::MYTHIC;

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $lightning = new PiggyLightning($event->getEntity()->getLocation());
            $lightning->setOwningEntity($player);
            $lightning->spawnToAll();
        }
    }
}