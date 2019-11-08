<?php


namespace DaPigGuy\PiggyCustomEnchants\enchants\traits;


use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Trait ToggleTrait
 * @package DaPigGuy\PiggyCustomEnchants\enchants\traits
 */
trait ToggleTrait
{
    /** @var array */
    public $stack;
    /** @var array */
    public $equippedArmorStack;

    /**
     * @return bool
     */
    public function canToggle(): bool
    {
        return true;
    }

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
        $perWorldDisabledEnchants = $this->plugin->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getLevel()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getLevel()->getFolderName()])) return;
        if ($this->getCooldown($player) > 0) return;
        if ($toggle) {
            $this->addToStack($player, $level);
        } else {
            $this->removeFromStack($player, $level);
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
     * @param Player $player
     * @param int $level
     */
    public function addToStack(Player $player, int $level): void
    {
        if (!isset($this->stack[$player->getName()])) $this->stack[$player->getName()] = 0;
        if (!isset($this->equippedArmorStack[$player->getName()])) $this->equippedArmorStack[$player->getName()] = 0;
        $this->stack[$player->getName()] += $level;
        $this->equippedArmorStack[$player->getName()]++;
    }

    /**
     * @param Player $player
     * @param int $level
     */
    public function removeFromStack(Player $player, int $level): void
    {
        if (isset($this->stack[$player->getName()])) $this->stack[$player->getName()] -= $level;
        if (isset($this->equippedArmorStack[$player->getName()])) $this->equippedArmorStack[$player->getName()]--;
    }
}