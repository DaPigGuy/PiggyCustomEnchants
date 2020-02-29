<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class DeathbringerEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class DeathbringerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Deathbringer";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["base" => 2, "multiplier" => 0.1];
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
            $event->setModifier($this->extraData["base"] + $level * $this->extraData["multiplier"], CustomEnchantIds::DEATHBRINGER);
        }
    }
}