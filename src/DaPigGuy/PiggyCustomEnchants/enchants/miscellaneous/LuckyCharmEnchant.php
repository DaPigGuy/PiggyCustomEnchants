<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class LuckyCharmEnchant extends ToggleableEnchantment
{
    public string $name = "Lucky Charm";
    public int $rarity = Rarity::MYTHIC;
    public int $maxLevel = 3;

    public int $usageType = CustomEnchant::TYPE_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_GLOBAL;

    public function getDefaultExtraData(): array
    {
        return ["additionalMultiplier" => 0.05];
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        /** @var ReactiveEnchantment $enchantment */
        foreach (CustomEnchantManager::getEnchantments() as $enchantment) {
            if ($enchantment->canReact()) {
                $enchantment->setChanceMultiplier($player, $enchantment->getChanceMultiplier($player) + ($toggle ? 1 : -1) * $level * $this->extraData["additionalMultiplier"]);
            }
        }
    }
}