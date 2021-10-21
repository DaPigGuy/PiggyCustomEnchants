<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class QuickeningEnchant extends ReactiveEnchantment
{
    public string $name = "Quickening";
    public int $rarity = Rarity::UNCOMMON;

    public int $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["duration" => 40, "baseAmplifier" => 1, "amplifierMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            if ($player->getEffects()->has(VanillaEffects::SPEED()) !== true) {
                $effect = new EffectInstance(VanillaEffects::SPEED(), $this->extraData["duration"], $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false);
                $player->getEffects()->add($effect);
            }
        }
    }
}