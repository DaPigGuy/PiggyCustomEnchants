<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class AntitoxinEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class AntitoxinEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Antitoxin";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityEffectAddEvent::class];
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
        if ($event instanceof EntityEffectAddEvent) {
            if ($event->getEffect()->getId() === Effect::POISON) $event->setCancelled();
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_HELMET;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_HELMET;
    }
}