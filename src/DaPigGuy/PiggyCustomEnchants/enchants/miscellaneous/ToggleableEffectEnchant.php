<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ToggleableEffectEnchant extends ToggleableEnchantment
{
    /** @var EffectInstance[] */
    private array $previousEffect = [];

    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $maxLevel, int $usageType, int $itemType, private Effect $effect, private int $baseAmplifier = 0, private int $amplifierMultiplier = 1, int $rarity = Rarity::RARE)
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->maxLevel = $maxLevel;
        $this->usageType = $usageType;
        $this->itemType = $itemType;
        parent::__construct($plugin, $id);
    }

    public function getDefaultExtraData(): array
    {
        return ["baseAmplifier" => $this->baseAmplifier, "amplifierMultiplier" => $this->amplifierMultiplier];
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if ($toggle) {
            if ($this->effect === VanillaEffects::JUMP_BOOST()) Utils::setShouldTakeFallDamage($player, false, 2147483647);
            if ($player->getEffects()->has($this->effect) && $player->getEffects()->get($this->effect)->getAmplifier() > $this->extraData["baseAmplifier"] + $this->extraData["amplifierMultiplier"] * $level) $this->previousEffect[$player->getName()] = $player->getEffects()->get($this->effect);
        } else {
            if ($this->usageType !== CustomEnchant::TYPE_ARMOR_INVENTORY || $this->getArmorStack($player) === 0) {
                if ($this->effect === VanillaEffects::JUMP_BOOST()) Utils::setShouldTakeFallDamage($player, true);
                $player->getEffects()->remove($this->effect);
                if (isset($this->previousEffect[$player->getName()])) {
                    $player->getEffects()->add($this->previousEffect[$player->getName()]);
                    unset($this->previousEffect[$player->getName()]);
                }
                return;
            }
        }
        $player->getEffects()->remove($this->effect);
        $amplifier = $this->extraData["baseAmplifier"] + $this->extraData["amplifierMultiplier"] * $level;
        $player->getEffects()->add(new EffectInstance($this->effect, 2147483647, min($amplifier, 255), false));
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