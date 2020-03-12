<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class OverloadEnchant extends ToggleableEnchantment
{
    /** @var string */
    public $name = "Overload";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_MYTHIC;
    /** @var int */
    public $maxLevel = 3;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getDefaultExtraData(): array
    {
        return ["multiplier" => 2];
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        $player->setMaxHealth($player->getMaxHealth() + $this->extraData["multiplier"] * $level * ($toggle ? 1 : -1));
        $player->setHealth($player->getHealth() * ($player->getMaxHealth() / ($player->getMaxHealth() - $this->extraData["multiplier"] * $level * ($toggle ? 1 : -1))));
    }
}