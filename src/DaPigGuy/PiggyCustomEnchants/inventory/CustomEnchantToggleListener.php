<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\inventory;

use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryChangeListener;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

/**
 * Class CustomEnchantToggleListener
 * @package DaPigGuy\PiggyCustomEnchants\inventory
 */
class CustomEnchantToggleListener implements InventoryChangeListener
{
    /** @var array */
    private $currentItems;

    /**
     * @param Inventory $inventory
     * @param int $slot
     */
    public function onSlotChange(Inventory $inventory, int $slot): void
    {
        if ($inventory instanceof PlayerInventory || $inventory instanceof ArmorInventory) {
            $player = $inventory->getHolder();
            if (!($oldItem = ($this->currentItems[$player->getName()][get_class($inventory)][$slot] ?? ItemFactory::get(ItemIds::AIR)))->equals(($newItem = $inventory->getItem($slot)), !$inventory instanceof ArmorInventory)) {
                if ($newItem->getId() === ItemIds::AIR || $inventory instanceof ArmorInventory) foreach ($oldItem->getEnchantments() as $oldEnchantment) ToggleableEnchantment::attemptToggle($player, $oldItem, $oldEnchantment, $inventory, $slot, false);
                if ($oldItem->getId() === ItemIds::AIR || $inventory instanceof ArmorInventory) foreach ($newItem->getEnchantments() as $newEnchantment) ToggleableEnchantment::attemptToggle($player, $newItem, $newEnchantment, $inventory, $slot);
                $this->currentItems[$inventory->getHolder()->getName()][get_class($inventory)][$slot] = $inventory->getItem($slot);
            }
        }
    }

    /**
     * @param Inventory $inventory
     */
    public function onContentChange(Inventory $inventory): void
    {
        for ($i = 0; $i < $inventory->getSize(); $i++) $this->onSlotChange($inventory, $i);
    }
}