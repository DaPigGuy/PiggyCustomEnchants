<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class BerserkerEnchant extends ReactiveEnchantment
{
    public string $name = "Berserker";
    public int $cooldownDuration = 300;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getReagent(): array
    {
        return [EntityDamageEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["minimumHealth" => 4, "effectDurationMultiplier" => 200, "effectAmplifierBase" => 3, "effectAmplifierMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageEvent) {
            if ($player->getHealth() - $event->getFinalDamage() <= $this->extraData["minimumHealth"]) {
                if (!$player->getEffects()->has(VanillaEffects::STRENGTH())) {
                    $effect = new EffectInstance(VanillaEffects::STRENGTH(), $this->extraData["effectDurationMultiplier"] * $level, $level * $this->extraData["effectAmplifierMultiplier"] + $this->extraData["effectAmplifierBase"], false);
                    $player->getEffects()->add($effect);
                }
                $player->sendMessage("Your bloodloss makes your stronger!");
            }
        }
    }
}