<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class FocusedEnchant extends RecursiveEnchant
{
    public string $name = "Focused";
    public int $rarity = CustomEnchant::RARITY_UNCOMMON;

    public int $usageType = CustomEnchant::TYPE_HELMET;
    public int $itemType = CustomEnchant::ITEM_TYPE_HELMET;

    public function getReagent(): array
    {
        return [EntityEffectAddEvent::class];
    }

    public function safeReact(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityEffectAddEvent) {
            $effect = $event->getEffect();
            if ($effect->getId() === Effect::NAUSEA) {
                if ($effect->getEffectLevel() - ($level * 2) <= 0) {
                    $event->setCancelled();
                } else {
                    $event->setCancelled();
                    $player->addEffect($effect->setAmplifier($effect->getEffectLevel() - (1 + ($level * 2))));
                }
            }
        }
    }
}