<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ProwlEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    public string $name = "Prowl";
    public int $maxLevel = 1;

    public int $usageType = CustomEnchant::TYPE_CHESTPLATE;
    public int $itemType = CustomEnchant::ITEM_TYPE_CHESTPLATE;

    /** @var bool[] */
    public array $prowled;

    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle): void
    {
        if (!$toggle && isset($this->prowled[$player->getName()])) {
            foreach ($player->getServer()->getOnlinePlayers() as $p) {
                $p->showPlayer($player);
            }
            $player->getEffects()->remove(VanillaEffects::SLOWNESS());
            if (!$player->getEffects()->has(VanillaEffects::INVISIBILITY())) {
                $player->setInvisible(false);
            }
            unset($this->prowled[$player->getName()]);
        }
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        if ($player->isSneaking()) {
            foreach ($player->getServer()->getOnlinePlayers() as $p) {
                $p->hidePlayer($player);
            }
            $effect = new EffectInstance(VanillaEffects::SLOWNESS(), 2147483647, 0, false);
            $player->setInvisible();
            $player->getEffects()->add($effect);
            $this->prowled[$player->getName()] = true;
        } else {
            if (isset($this->prowled[$player->getName()])) {
                foreach ($player->getServer()->getOnlinePlayers() as $p) {
                    $p->showPlayer($player);
                }
                $player->getEffects()->remove(VanillaEffects::SLOWNESS());
                if (!$player->getEffects()->has(VanillaEffects::INVISIBILITY())) {
                    $player->setInvisible(false);
                }
                unset($this->prowled[$player->getName()]);
            }
        }
    }
}