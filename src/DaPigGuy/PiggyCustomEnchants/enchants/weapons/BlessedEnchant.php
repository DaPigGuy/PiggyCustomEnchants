<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class BlessedEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class BlessedEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Blessed";
    /** @var int */
    public $maxLevel = 3;

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
            foreach ($player->getEffects() as $effect) {
                if ($effect->getType()->isBad()) {
                    $player->removeEffect($effect->getId());
                }
            }
        }
    }
}