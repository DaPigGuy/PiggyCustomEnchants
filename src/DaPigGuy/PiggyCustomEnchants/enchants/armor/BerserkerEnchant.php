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

class BerserkerEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Berserker";
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
        return ["minimumHealth" => 4, "effectDurationMultiplier" => 200, "effectAmplifierBase" => 3, "effectAmplifierMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageEvent) {
            if ($player->getHealth() - $event->getFinalDamage() <= $this->extraData["minimumHealth"]) {
                if (!$player->hasEffect(Effect::STRENGTH)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::STRENGTH), $this->extraData["effectDurationMultiplier"] * $level, $level * $this->extraData["effectAmplifierMultiplier"] + $this->extraData["effectAmplifierBase"], false);
                    $player->addEffect($effect);
                }
                $player->sendMessage("Your bloodloss makes your stronger!");
            }
        }
    }
}