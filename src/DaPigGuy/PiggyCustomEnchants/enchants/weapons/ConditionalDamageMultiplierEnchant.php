<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class ConditionalDamageMultiplierEnchant extends ReactiveEnchantment
{
    /** @var callable */
    private $condition;

    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, callable $condition, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->condition = $condition;
        parent::__construct($plugin, $id);
    }

    public function getDefaultExtraData(): array
    {
        return ["additionalMultiplier" => 0.1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            if (($this->condition)($event)) {
                $event->setModifier($this->extraData["additionalMultiplier"] * $level, $this->getId());
            }
        }
    }
}