<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class VampireEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Vampire";
    /** @var int */
    public $rarity = Rarity::UNCOMMON;
    /** @var int */
    public $maxLevel = 1;
    /** @var int */
    public $cooldownDuration = 5;

    public function getDefaultExtraData(): array
    {
        return ["healthMultiplier" => 0.5, "foodMultiplier" => 0.5];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $player->setHealth($player->getHealth() + ($event->getFinalDamage() * $this->extraData["healthMultiplier"]) > $player->getMaxHealth() ? $player->getMaxHealth() : $player->getHealth() + ($event->getFinalDamage() * $this->extraData["healthMultiplier"]));
            $player->getHungerManager()->setFood($player->getHungerManager()->getFood() + ($event->getFinalDamage() * $this->extraData["foodMultiplier"]) > $player->getHungerManager()->getMaxFood() ? $player->getHungerManager()->getMaxFood() : $player->getHungerManager()->getFood() + ($event->getFinalDamage() * $this->extraData["foodMultiplier"]));
        }
    }
}