<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\block\BlockLegacyIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class ParachuteEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate
 */
class ParachuteEnchant extends TickingEnchantment
{
    use ToggleTrait;

    /** @var string */
    public $name = "Parachute";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        /*
        if ($this->isInAir($player) && !$player->getAllowFlight() && !$player->canClimbWalls() && (($enchantInstance = $player->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantIds::JETPACK)) === null || !($enchant = $enchantInstance->getType()) instanceof JetpackEnchant || !$enchant->hasActiveJetpack($player))) {
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 2147483647, -5, false)); //Hack to make the Parachute CE feel like a parachute
        } elseif (($effect = $player->getEffect(Effect::LEVITATION)) !== null && $effect->getAmplifier() === -5) {
            if ($this->isInAir($player) || $player->getWorld()->getBlock($player->subtract(0, 1))->getId() !== BlockLegacyIds::AIR) $player->removeEffect($effect->getId());
        }
       */
        $player->resetFallDistance();
    }

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
        //if (!$toggle && ($effect = $player->getEffect(Effect::LEVITATION)) !== null && $effect->getAmplifier() === -5) {
        //    $player->removeEffect($effect->getId());
        //}
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_CHESTPLATE;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_CHESTPLATE;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function isInAir(Player $player): bool
    {
        for ($y = 1; $y <= 5; $y++) {
            if ($player->getWorld()->getBlock($player->getPosition()->subtract(0, $y))->getId() !== BlockLegacyIds::AIR) return false;
        }
        return true;
    }
}