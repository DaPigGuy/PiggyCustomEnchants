<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class ShieldedEnchant extends ToggleableEnchantment
{
    /** @var string */
    public $name = "Shielded";
    /** @var int */
    public $maxLevel = 3;

    /** @var EffectInstance[] */
    private $previousEffect;

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if ($toggle) {
            if ($player->hasEffect(Effect::RESISTANCE) && $player->getEffect(Effect::RESISTANCE)->getAmplifier() > $this->stack[$player->getName()] - 1) $this->previousEffect[$player->getName()] = $player->getEffect(Effect::RESISTANCE);
        } else {
            if ($this->equippedArmorStack[$player->getName()] === 0) {
                $player->removeEffect(Effect::RESISTANCE);
                if (isset($this->previousEffect[$player->getName()])) {
                    $player->addEffect($this->previousEffect[$player->getName()]);
                    unset($this->previousEffect[$player->getName()]);
                }
                return;
            }
        }
        $player->removeEffect(Effect::RESISTANCE);
        $player->addEffect(new EffectInstance(Effect::getEffect(Effect::RESISTANCE), 2147483647, $this->stack[$player->getName()] - 1, false));
    }

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }

    public function canEffectsStack(): bool
    {
        return true;
    }
}