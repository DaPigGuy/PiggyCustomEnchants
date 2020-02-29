<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class EnlightedEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class EnlightedEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Enlighted";


    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["durationMultiplier" => 60, "baseAmplifier" => 0, "amplifierMultiplier" => 1];
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
        if ($event instanceof EntityDamageByEntityEvent) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), $this->extraData["durationMultiplier"] * $level, $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false));
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}