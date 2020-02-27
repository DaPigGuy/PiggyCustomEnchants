<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class ShieldedEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class ShieldedEnchant extends ToggleableEnchantment
{
    /** @var string */
    public $name = "Shielded";
    /** @var int */
    public $maxLevel = 3;

    /** @var EffectInstance[] */
    private $previousEffect;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     * @param bool $toggle
     */
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if ($toggle) {
            if ($player->getEffects()->has(VanillaEffects::RESISTANCE()) && $player->getEffects()->get(VanillaEffects::RESISTANCE())->getAmplifier() > $this->stack[$player->getName()] - 1) $this->previousEffect[$player->getName()] = $player->getEffects()->get(VanillaEffects::RESISTANCE());
        } else {
            if ($this->equippedArmorStack[$player->getName()] === 0) {
                $player->getEffects()->remove(VanillaEffects::RESISTANCE());
                if (isset($this->previousEffect[$player->getName()])) {
                    $player->getEffects()->add($this->previousEffect[$player->getName()]);
                    unset($this->previousEffect[$player->getName()]);
                }
                return;
            }
        }
        $player->getEffects()->remove(VanillaEffects::RESISTANCE());
        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483647, $this->stack[$player->getName()] - 1, false));
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }

    /**
     * @return bool
     */
    public function canEffectsStack(): bool
    {
        return true;
    }
}