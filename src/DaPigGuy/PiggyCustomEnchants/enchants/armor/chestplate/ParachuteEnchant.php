<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\boots\JetpackEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\block\BlockTypeIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ParachuteEnchant extends TickingEnchantment
{
    use ToggleTrait;

    public string $name = "Parachute";
    public int $rarity = Rarity::UNCOMMON;
    public int $maxLevel = 1;

    public int $usageType = CustomEnchant::TYPE_CHESTPLATE;
    public int $itemType = CustomEnchant::ITEM_TYPE_CHESTPLATE;

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($this->isInAir($player) && !$player->getAllowFlight() && !$player->canClimbWalls() && (($enchantInstance = $player->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantManager::getEnchantment(CustomEnchantIds::JETPACK))) === null || !($enchant = $enchantInstance->getType()) instanceof JetpackEnchant || !$enchant->hasActiveJetpack($player))) {
            $player->getEffects()->add(new EffectInstance(PiggyCustomEnchants::$SLOW_FALL, 2147483647, 1, false));
        } elseif ($player->getEffects()->get(PiggyCustomEnchants::$SLOW_FALL) !== null) {
            if ($this->isInAir($player) || $player->getWorld()->getBlock($player->getPosition()->subtract(0, 1, 0))->getTypeId() !== BlockTypeIds::AIR) $player->getEffects()->remove(PiggyCustomEnchants::$SLOW_FALL);
        }
        $player->resetFallDistance();
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && ($effect = $player->getEffects()->get(PiggyCustomEnchants::$SLOW_FALL)) !== null && $effect->getAmplifier() === -5) {
            $player->getEffects()->remove(PiggyCustomEnchants::$SLOW_FALL);
        }
    }

    public function isInAir(Player $player): bool
    {
        for ($y = 1; $y <= 5; $y++) {
            if ($player->getWorld()->getBlock($player->getPosition()->subtract(0, $y, 0))->getTypeId() !== BlockTypeIds::AIR) return false;
        }
        return true;
    }
}