<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class ToggleableEffectEnchant extends ToggleableEnchantment
{
    /** @var int */
    private $effect;
    /** @var int */
    private $baseAmplifier;
    /** @var int */
    private $amplifierMultiplier;

    /** @var EffectInstance[] */
    private $previousEffect;

    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $maxLevel, int $usageType, int $itemType, int $effect, int $baseAmplifier = 0, int $amplifierMultiplier = 1, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->maxLevel = $maxLevel;
        $this->usageType = $usageType;
        $this->itemType = $itemType;
        $this->effect = $effect;
        $this->baseAmplifier = $baseAmplifier;
        $this->amplifierMultiplier = $amplifierMultiplier;
        parent::__construct($plugin, $id);
    }

    public function getDefaultExtraData(): array
    {
        return ["baseAmplifier" => $this->baseAmplifier, "amplifierMultiplier" => $this->amplifierMultiplier];
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if ($toggle) {
            if ($this->effect === Effect::JUMP) Utils::setShouldTakeFallDamage($player, false, 2147483647);
            if ($player->hasEffect($this->effect) && $player->getEffect($this->effect)->getAmplifier() > $this->extraData["baseAmplifier"] + $this->extraData["amplifierMultiplier"] * $level) $this->previousEffect[$player->getName()] = $player->getEffect($this->effect);
        } else {
            if ($this->usageType !== CustomEnchant::TYPE_ARMOR_INVENTORY || $this->equippedArmorStack[$player->getName()] === 0) {
                if ($this->effect === Effect::JUMP) Utils::setShouldTakeFallDamage($player, true);
                $player->removeEffect($this->effect);
                if (isset($this->previousEffect[$player->getName()])) {
                    $player->addEffect($this->previousEffect[$player->getName()]);
                    unset($this->previousEffect[$player->getName()]);
                }
                return;
            }
        }
        $player->removeEffect($this->effect);
        $player->addEffect(new EffectInstance(Effect::getEffect($this->effect), 2147483647, $this->extraData["baseAmplifier"] + $this->extraData["amplifierMultiplier"] * $level, false));
    }

    public function getUsageType(): int
    {
        return $this->usageType;
    }

    public function getItemType(): int
    {
        return $this->itemType;
    }
}