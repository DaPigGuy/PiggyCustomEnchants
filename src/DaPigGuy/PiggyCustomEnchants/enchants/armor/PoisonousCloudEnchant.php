<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\color\Color;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\particle\DustParticle;

class PoisonousCloudEnchant extends TickingEnchantment
{
    public string $name = "Poisonous Cloud";
    public int $maxLevel = 3;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 3, "durationMultiplier" => 100, "baseAmplifier" => -1, "amplifierMultiplier" => 1];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $radius = $level * $this->extraData["radiusMultiplier"];
        foreach ($player->getWorld()->getEntities() as $entity) {
            if ($entity !== $player && $entity instanceof Living && !AllyChecks::isAlly($player, $entity) && $entity->getPosition()->distance($player->getPosition()) <= $radius) {
                $effect = new EffectInstance(VanillaEffects::POISON(), $level * $this->extraData["durationMultiplier"], $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false);
                $entity->getEffects()->add($effect);
            }
        }
        if ($player->getServer()->getTick() % 20 === 0) {
            for ($x = -$radius; $x <= $radius; $x += 0.25) {
                for ($y = -$radius; $y <= $radius; $y += 0.25) {
                    for ($z = -$radius; $z <= $radius; $z += 0.25) {
                        $random = mt_rand(1, 800 * $level);
                        if ($random === 800 * $level) {
                            $player->getWorld()->addParticle($player->getPosition()->add($x, $y, $z), new DustParticle(new Color(34, 139, 34)));
                        }
                    }
                }
            }
        }
    }
}