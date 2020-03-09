<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\tools;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class EnergizingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Energizing";

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_TOOLS;

    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["duration" => 20, "baseAmplifier" => -1, "amplifierMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            if ($player->hasEffect(Effect::HASTE) !== true) {
                $effect = new EffectInstance(Effect::getEffect(Effect::HASTE), $this->extraData["duration"], $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false);
                $player->addEffect($effect);
            }
        }
    }
}