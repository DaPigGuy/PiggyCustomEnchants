<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class EndershiftEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Endershift";
    /** @var int */
    public $cooldownDuration = 300;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getReagent(): array
    {
        return [EntityDamageEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["speedDurationMultiplier" => 200, "speedBaseAmplifier" => 3, "speedAmplifierMultiplier" => 1, "strengthDurationMultiplier" => 200, "strengthBaseAmplifier" => 3, "strengthAmplifierMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageEvent) {
            if ($player->getHealth() - $event->getFinalDamage() <= 4) {
                if (!$player->hasEffect(Effect::SPEED)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::SPEED), $this->extraData["speedDurationMultiplier"] * $level, $level * $this->extraData["speedAmplifierMultiplier"] + $this->extraData["speedBaseAmplifier"], false);
                    $player->addEffect($effect);
                }
                if (!$player->hasEffect(Effect::ABSORPTION)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::ABSORPTION), $this->extraData["strengthDurationMultiplier"] * $level, $level * $this->extraData["strengthAmplifierMultiplier"] + $this->extraData["strengthBaseAmplifier"], false);
                    $player->addEffect($effect);
                }
                $player->sendMessage("You feel a rush of energy coming from your armor!");
            }
        }
    }
}