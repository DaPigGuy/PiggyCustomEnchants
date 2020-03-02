<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class FocusedEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Focused";

    public function getReagent(): array
    {
        return [EntityEffectAddEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityEffectAddEvent) {
            $effect = $event->getEffect();
            if ($effect->getType() === VanillaEffects::NAUSEA()) {
                if ($effect->getEffectLevel() - ($level * 2) <= 0) {
                    $event->setCancelled();
                } else {
                    $event->setCancelled();
                    $this->setCooldown($player, 1);
                    $player->getEffects()->remove($effect->getType());
                    $player->getEffects()->add($effect->setAmplifier($effect->getEffectLevel() - (1 + ($level * 2))));
                }
            }
        }
    }

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_HELMET;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_HELMET;
    }
}