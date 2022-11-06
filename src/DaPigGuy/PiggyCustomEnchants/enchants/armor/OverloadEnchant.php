<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class OverloadEnchant extends ToggleableEnchantment
{
    public string $name = "Overload";
    public int $rarity = Rarity::MYTHIC;
    public int $maxLevel = 3;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getDefaultExtraData(): array
    {
        return ["multiplier" => 2];
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        $maxHealth = $player->getMaxHealth() + $this->extraData["multiplier"] * $level * ($toggle ? 1 : -1);
        $health = $player->getHealth() * ($player->getMaxHealth() / ($player->getMaxHealth() - $this->extraData["multiplier"] * $level * ($toggle ? 1 : -1)));

        $player->setMaxHealth(max($maxHealth, 1));
        $player->setHealth(max($health, 0));
    }
}