<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class ParalyzeEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Paralyze";

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

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
                if (!$entity->hasEffect(Effect::SLOWNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), $this->extraData["slownessBaseDuration"] + $level * $this->extraData["slownessDurationMultiplier"], $this->extraData["slownessBaseAmplifier"] + $level * $this->extraData["slownessAmplifierMultiplier"], false);
                    $entity->addEffect($effect);
                }
                if (!$entity->hasEffect(Effect::BLINDNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::BLINDNESS), $this->extraData["blindnessBaseDuration"] + $level * $this->extraData["blindnessDurationMultiplier"], 1, false);
                    $entity->addEffect($effect);
                }
                if (!$entity->hasEffect(Effect::WEAKNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::WEAKNESS), $this->extraData["weaknessBaseDuration"] + $level * $this->extraData["weaknessDurationMultiplier"], $this->extraData["weaknessBaseAmplifier"] + $level * $this->extraData["weaknessAmplifierMultiplier"], false);
                    $entity->addEffect($effect);
                }
            }
        }
    }
}