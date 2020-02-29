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

/**
 * Class EnergizingEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\tools
 */
class EnergizingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Energizing";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [BlockBreakEvent::class];
    }

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["duration" => 20, "baseAmplifier" => -1, "amplifierMultiplier" => 1];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof BlockBreakEvent) {
            if ($player->hasEffect(Effect::HASTE) !== true) {
                $effect = new EffectInstance(Effect::getEffect(Effect::HASTE), $this->extraData["duration"], $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false);
                $player->addEffect($effect);
            }
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_TOOLS;
    }
}