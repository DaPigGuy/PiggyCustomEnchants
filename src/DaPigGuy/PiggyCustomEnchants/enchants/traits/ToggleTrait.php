<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\traits;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\player\Player;

trait ToggleTrait
{
    protected PiggyCustomEnchants $plugin;

    /** @var int[] */
    public array $stack = [];
    /** @var int[] */
    public array $equippedArmorStack = [];

    public function canToggle(): bool
    {
        return true;
    }

    public function onToggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        $perWorldDisabledEnchants = $this->plugin->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getWorld()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getWorld()->getFolderName()])) return;
        if ($this->getCooldown($player) > 0) return;
        $toggle ? $this->addToStack($player, $level) : $this->removeFromStack($player, $level);
        $this->toggle($player, $item, $inventory, $slot, $level, $toggle);
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
    }

    public function addToStack(Player $player, int $level): void
    {
        $this->stack[$player->getName()] = $this->getStack($player) + $level;
        $this->equippedArmorStack[$player->getName()] = $this->getArmorStack($player) + 1;
    }

    public function removeFromStack(Player $player, int $level): void
    {
        if (isset($this->stack[$player->getName()])) $this->stack[$player->getName()] -= $level;
        $this->equippedArmorStack[$player->getName()] = $this->getArmorStack($player) - 1;
    }

    public function getStack(Player $player): int
    {
        if (isset($this->stack[$player->getName()])) return $this->stack[$player->getName()];
        return 0;
    }

    public function getArmorStack(Player $player): int
    {
        if (isset($this->equippedArmorStack[$player->getName()])) return $this->equippedArmorStack[$player->getName()];
        return 0;
    }

    public static function attemptToggle(Player $player, Item $item, EnchantmentInstance $enchantmentInstance, Inventory $inventory, int $slot, bool $toggle = true): void
    {
        /** @var ToggleableEnchantment $enchantment */
        $enchantment = $enchantmentInstance->getType();
        if (
            $enchantment instanceof CustomEnchant && $enchantment->canToggle() && (
                $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY ||
                ($enchantment->getUsageType() === CustomEnchant::TYPE_HAND && $inventory instanceof PlayerInventory && $inventory->getHeldItemIndex() === $slot) ||
                (
                    $inventory instanceof ArmorInventory && (
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_HELMET && Utils::isHelmet($item) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_CHESTPLATE && Utils::isChestplate($item) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_LEGGINGS && Utils::isLeggings($item) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_BOOTS && Utils::isBoots($item)
                    )
                )
            )
        ) {
            $enchantment->onToggle($player, $item, $inventory, $slot, $enchantmentInstance->getLevel(), $toggle);
        }
    }
}