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
 * Class VampireEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class VampireEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Vampire";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityDamageByEntityEvent::class];
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
            $player->setHealth($player->getHealth() + ($event->getFinalDamage() / 2));
            $player->setFood($player->getFood() + ($event->getFinalDamage() / 2));
            if ($player->getHealth() > $player->getMaxHealth()) $player->setHealth($player->getMaxHealth());
            if ($player->getFood() > $player->getMaxFood()) $player->setFood($player->getMaxFood());
            $this->setCooldown($player, 5);
        }
    }
}