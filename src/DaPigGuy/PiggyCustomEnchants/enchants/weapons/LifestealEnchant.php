<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class LifestealEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class LifestealEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Lifesteal";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["base" => 2, "multiplier" => 1];
    }

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
            $player->setHealth($player->getHealth() + $this->extraData["base"] + $level * $this->extraData["multiplier"] > $player->getMaxHealth() ? $player->getMaxHealth() : $player->getHealth() + $this->extraData["base"] + $level * $this->extraData["multiplier"]);
        }
    }
}