<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\traits;

use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Trait TickingTrait
 * @package DaPigGuy\PiggyCustomEnchants\enchants\traits
 */
trait TickingTrait
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    /**
     * @return bool
     */
    public function canTick(): bool
    {
        return true;
    }

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
        $perWorldDisabledEnchants = $this->plugin->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getWorld()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getWorld()->getFolderName()])) return;
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