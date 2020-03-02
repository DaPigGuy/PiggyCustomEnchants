<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class EnlightedEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Enlighted";

    public function getDefaultExtraData(): array
    {
        return ["durationMultiplier" => 60, "baseAmplifier" => 0, "amplifierMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), $this->extraData["durationMultiplier"] * $level, $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false));
        }
    }

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}