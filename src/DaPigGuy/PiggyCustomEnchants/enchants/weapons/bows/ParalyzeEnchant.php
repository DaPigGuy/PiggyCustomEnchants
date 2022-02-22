<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ParalyzeEnchant extends ReactiveEnchantment
{
    public string $name = "Paralyze";

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return [
            "slownessBaseDuration" => 40,
            "slownessDurationMultiplier" => 20,
            "slownessBaseAmplifier" => 4,
            "slownessAmplifierMultiplier" => 1,
            "blindnessBaseDuration" => 40,
            "blindnessDurationMultiplier" => 20,
            "weaknessBaseDuration" => 40,
            "weaknessDurationMultiplier" => 20,
            "weaknessBaseAmplifier" => 4,
            "weaknessAmplifierMultiplier" => 1,
        ];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Living) {
                if (!$entity->getEffects()->has(VanillaEffects::SLOWNESS())) {
                    $effect = new EffectInstance(VanillaEffects::SLOWNESS(), $this->extraData["slownessBaseDuration"] + $level * $this->extraData["slownessDurationMultiplier"], $this->extraData["slownessBaseAmplifier"] + $level * $this->extraData["slownessAmplifierMultiplier"], false);
                    $entity->getEffects()->add($effect);
                }
                if (!$entity->getEffects()->has(VanillaEffects::BLINDNESS())) {
                    $effect = new EffectInstance(VanillaEffects::BLINDNESS(), $this->extraData["blindnessBaseDuration"] + $level * $this->extraData["blindnessDurationMultiplier"], 1, false);
                    $entity->getEffects()->add($effect);
                }
                if (!$entity->getEffects()->has(VanillaEffects::WEAKNESS())) {
                    $effect = new EffectInstance(VanillaEffects::WEAKNESS(), $this->extraData["weaknessBaseDuration"] + $level * $this->extraData["weaknessDurationMultiplier"], $this->extraData["weaknessBaseAmplifier"] + $level * $this->extraData["weaknessAmplifierMultiplier"], false);
                    $entity->getEffects()->add($effect);
                }
            }
        }
    }
}