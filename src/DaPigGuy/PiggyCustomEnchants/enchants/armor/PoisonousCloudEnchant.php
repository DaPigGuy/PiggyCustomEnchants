<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\DustParticle;
use pocketmine\Player;

class PoisonousCloudEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Poisonous Cloud";
    /** @var int */
    public $maxLevel = 3;

    /** @var int */
    public $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 3, "durationMultiplier" => 100, "baseAmplifier" => -1, "amplifierMultiplier" => 1];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $radius = $level * $this->extraData["radiusMultiplier"];
        foreach ($player->getLevel()->getEntities() as $entity) {
            if ($entity !== $player && $entity instanceof Living && !AllyChecks::isAlly($player, $entity) && $entity->distance($player) <= $radius) {
                $effect = new EffectInstance(Effect::getEffect(Effect::POISON), $level * $this->extraData["durationMultiplier"], $level * $this->extraData["amplifierMultiplier"] + $this->extraData["baseAmplifier"], false);
                $entity->addEffect($effect);
            }
        }
        if ($player->getServer()->getTick() % 20 === 0) {
            for ($x = -$radius; $x <= $radius; $x += 0.25) {
                for ($y = -$radius; $y <= $radius; $y += 0.25) {
                    for ($z = -$radius; $z <= $radius; $z += 0.25) {
                        $random = mt_rand(1, 800 * $level);
                        if ($random === 800 * $level) {
                            $player->getLevel()->addParticle(new DustParticle($player->add($x, $y, $z), 34, 139, 34));
                        }
                    }
                }
            }
        }
    }
}