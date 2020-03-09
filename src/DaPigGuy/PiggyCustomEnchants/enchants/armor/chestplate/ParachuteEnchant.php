<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\armor\boots\JetpackEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class ParachuteEnchant extends TickingEnchantment
{
    use ToggleTrait;

    /** @var string */
    public $name = "Parachute";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_CHESTPLATE;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_CHESTPLATE;

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($this->isInAir($player) && !$player->getAllowFlight() && !$player->canClimbWalls() && (($enchantInstance = $player->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantIds::JETPACK)) === null || !($enchant = $enchantInstance->getType()) instanceof JetpackEnchant || !$enchant->hasActiveJetpack($player))) {
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::LEVITATION), 2147483647, -5, false)); //Hack to make the Parachute CE feel like a parachute
        } elseif (($effect = $player->getEffect(Effect::LEVITATION)) !== null && $effect->getAmplifier() === -5) {
            if ($this->isInAir($player) || $player->getLevel()->getBlock($player->subtract(0, 1))->getId() !== Block::AIR) $player->removeEffect($effect->getId());
        }
        $player->resetFallDistance();
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && ($effect = $player->getEffect(Effect::LEVITATION)) !== null && $effect->getAmplifier() === -5) {
            $player->removeEffect($effect->getId());
        }
    }

    public function isInAir(Player $player): bool
    {
        for ($y = 1; $y <= 5; $y++) {
            if ($player->getLevel()->getBlock($player->subtract(0, $y))->getId() !== Block::AIR) return false;
        }
        return true;
    }
}