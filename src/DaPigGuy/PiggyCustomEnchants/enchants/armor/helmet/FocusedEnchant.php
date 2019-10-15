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
 * Class FocusedEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet
 */
class FocusedEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Focused";

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
            $effect = $event->getEffect();
            if ($effect->getId() === Effect::NAUSEA) {
                if ($effect->getEffectLevel() - ($level * 2) <= 0) {
                    $event->setCancelled();
                } else {
                    $event->setCancelled();
                    $this->setCooldown($player, 1);
                    $player->addEffect($effect->setAmplifier($effect->getEffectLevel() - (1 + ($level * 2))));
                }
            }
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