<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class TickingEnchantment
 * @package DaPigGuy\PiggyCustomEnchants\enchants
 */
class TickingEnchantment extends CustomEnchant
{
    /**
     * @return int
     */
    public function getTickingInterval(): int
    {
        return 1;
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function onTick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $perWorldDisabledEnchants = CustomEnchantManager::getPlugin()->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getLevel()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getLevel()->getFolderName()])) return;
        if ($this->getCooldown($player) > 0) return;
        $this->tick($player, $item, $inventory, $slot, $level);
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {

    }

    /**
     * @return bool
     */
    public function supportsMultipleItems(): bool
    {
        return false;
    }
}