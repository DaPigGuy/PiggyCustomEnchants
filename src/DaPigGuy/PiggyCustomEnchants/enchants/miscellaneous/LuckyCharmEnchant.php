<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class LuckyCharmEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous
 */
class LuckyCharmEnchant extends ToggleableEnchantment
{
    /** @var string */
    public $name = "Lucky Charm";
    /** @var int */
    public $maxLevel = 3;

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["additionalMultiplier" => 0.05];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     * @param bool $toggle
     */
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        /** @var ReactiveEnchantment $enchantment */
        foreach (CustomEnchantManager::getEnchantments() as $enchantment) {
            if ($enchantment->canReact()) {
                $enchantment->setChanceMultiplier($player, $enchantment->getChanceMultiplier($player) + ($toggle ? 1 : -1) * $level * $this->extraData["additionalMultiplier"]);
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return self::TYPE_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_GLOBAL;
    }
}