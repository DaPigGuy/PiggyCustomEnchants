<?php


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
use pocketmine\Player;

trait ToggleTrait
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    /** @var array */
    public $stack;
    /** @var array */
    public $equippedArmorStack;

    public function canToggle(): bool
    {
        return true;
    }

    public function onToggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
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

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
    }

    public function addToStack(Player $player, int $level): void
    {
        if (!isset($this->stack[$player->getName()])) $this->stack[$player->getName()] = 0;
        if (!isset($this->equippedArmorStack[$player->getName()])) $this->equippedArmorStack[$player->getName()] = 0;
        $this->stack[$player->getName()] += $level;
        $this->equippedArmorStack[$player->getName()]++;
    }

    public function removeFromStack(Player $player, int $level): void
    {
        if (isset($this->stack[$player->getName()])) $this->stack[$player->getName()] -= $level;
        if (isset($this->equippedArmorStack[$player->getName()])) $this->equippedArmorStack[$player->getName()]--;
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