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

class ShieldedEnchant extends ToggleableEnchantment
{
    public string $name = "Shielded";
    public int $maxLevel = 3;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    /** @var EffectInstance[] */
    private array $previousEffect;

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if ($toggle) {
            if ($player->getEffects()->has(VanillaEffects::RESISTANCE()) && $player->getEffects()->get(VanillaEffects::RESISTANCE())->getAmplifier() > $this->getStack($player) - 1) $this->previousEffect[$player->getName()] = $player->getEffects()->get(VanillaEffects::RESISTANCE());
        } else {
            if ($this->getArmorStack($player) === 0) {
                $player->getEffects()->remove(VanillaEffects::RESISTANCE());
                if (isset($this->previousEffect[$player->getName()])) {
                    $player->getEffects()->add($this->previousEffect[$player->getName()]);
                    unset($this->previousEffect[$player->getName()]);
                }
                return;
            }
        }
        $player->getEffects()->remove(VanillaEffects::RESISTANCE());

        $amplifier = $this->getStack($player) - 1;
        if ($amplifier < 0) $amplifier = 0;

        $player->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 2147483647, $amplifier, false));
    }

    public function canEffectsStack(): bool
    {
        return true;
    }
}