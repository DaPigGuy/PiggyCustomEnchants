<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class ToggleableEnchantment
 * @package DaPigGuy\PiggyCustomEnchants\enchants
 */
class ToggleableEnchantment extends CustomEnchant
{
    /** @var array */
    public $stack;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     * @param bool $toggle
     */
    public function onToggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle)
    {
        $perWorldDisabledEnchants = CustomEnchantManager::getPlugin()->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getLevel()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getLevel()->getFolderName()])) return;
        if ($this->getCooldown($player) > 0) return;
        if ($this->canEffectsStack()) {
            if ($toggle) {
                $this->addToStack($player, $level);
            } else {
                $this->removeFromStack($player, $level);
            }
        }
        $this->toggle($player, $item, $inventory, $slot, $level, $toggle);
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     * @param bool $toggle
     */
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle)
    {
    }

    /**
     * @return bool
     */
    public function canEffectsStack(): bool
    {
        return false;
    }

    /**
     * @param Player $player
     * @param int $level
     */
    public function addToStack(Player $player, int $level): void
    {
        if (!isset($this->stack[$player->getName()])) $this->stack[$player->getName()] = 0;
        $this->stack[$player->getName()] += $level;
    }

    /**
     * @param Player $player
     * @param int $level
     */
    public function removeFromStack(Player $player, int $level): void
    {
        if (isset($this->stack[$player->getName()])) $this->stack[$player->getName()] -= $level;
    }
}