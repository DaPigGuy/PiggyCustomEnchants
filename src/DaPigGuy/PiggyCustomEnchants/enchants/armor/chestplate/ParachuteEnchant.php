<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\armor\boots\JetpackEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\ToggleTrait;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Color;

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
        $slowFall = new Effect(27, "%potion.slowFalling", new Color(206, 255, 255));
        if ($this->isInAir($player) && !$player->getAllowFlight() && !$player->canClimbWalls() && (($enchantInstance = $player->getArmorInventory()->getBoots()->getEnchantment(Enchantment::get(CustomEnchantIds::JETPACK))) === null || !($enchant = $enchantInstance->getType()) instanceof JetpackEnchant || !$enchant->hasActiveJetpack($player))) {
            $player->getEffects()->add(new EffectInstance($slowFall, 2147483647, 1, false));
        } elseif (($effect = $player->getEffects()->get($slowFall)) !== null) {
            if ($this->isInAir($player) || $player->getWorld()->getBlock($player->getPosition()->subtract(0, 1))->getId() !== BlockLegacyIds::AIR) $player->getEffects()->remove($slowFall);
        }
        $player->resetFallDistance();
    }

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        $slowFall = new Effect(27, "%potion.slowFalling", new Color(206, 255, 255));
        if (!$toggle && ($effect = $player->getEffects()->get($slowFall)) !== null && $effect->getAmplifier() === -5) {
            $player->getEffects()->remove($slowFall);
        }
    }

    public function isInAir(Player $player): bool
    {
        for ($y = 1; $y <= 5; $y++) {
            if ($player->getWorld()->getBlock($player->getPosition()->subtract(0, $y))->getId() !== BlockLegacyIds::AIR) return false;
        }
        return true;
    }
}