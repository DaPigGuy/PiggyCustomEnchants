<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous;

use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use ReflectionException;

/**
 * Class ToggleableEffectEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous
 */
class ToggleableEffectEnchant extends ToggleableEnchantment
{
    /** @var int */
    private $effect;
    /** @var int */
    private $baseAmplifier = 0;
    /** @var int */
    private $amplifierMultiplier = 1;

    /** @var int */
    private $usageType;
    /** @var int */
    private $itemType;

    /** @var EffectInstance[] */
    private $previousEffect;

    /**
     * ToggleableEffectEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param string $name
     * @param int $maxLevel
     * @param int $usageType
     * @param int $itemType
     * @param int $effect
     * @param int $baseAmplifier
     * @param int $amplifierMultiplier
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, int $maxLevel, int $usageType, int $itemType, int $effect, int $baseAmplifier = 0, int $amplifierMultiplier = 1, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->maxLevel = $maxLevel;
        $this->usageType = $usageType;
        $this->itemType = $itemType;
        $this->effect = $effect;
        $this->baseAmplifier = $baseAmplifier;
        $this->amplifierMultiplier = $amplifierMultiplier;
        parent::__construct($plugin, $id, $rarity);
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
        if ($toggle) {
            if ($this->effect === Effect::JUMP) Utils::setShouldTakeFallDamage($player, false, 2147483647);
            if ($player->hasEffect($this->effect) && $player->getEffect($this->effect)->getAmplifier() > $this->baseAmplifier + $this->amplifierMultiplier * $level) $this->previousEffect[$player->getName()] = $player->getEffect($this->effect);
            $player->removeEffect($this->effect);
            $player->addEffect(new EffectInstance(Effect::getEffect($this->effect), 2147483647, $this->baseAmplifier + $this->amplifierMultiplier * $level, false));
        } else {
            if ($this->effect === Effect::JUMP) Utils::setShouldTakeFallDamage($player, true);
            $player->removeEffect($this->effect);
            if (isset($this->previousEffect[$player->getName()])) {
                $player->addEffect($this->previousEffect[$player->getName()]);
                unset($this->previousEffect[$player->getName()]);
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return $this->usageType;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return $this->itemType;
    }
}