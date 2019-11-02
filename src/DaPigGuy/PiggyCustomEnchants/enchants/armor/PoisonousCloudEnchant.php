<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\DustParticle;
use pocketmine\Player;

/**
 * Class PoisonousCloudEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class PoisonousCloudEnchant extends TickingEnchantment
{
    /** @var string */
    public $name = "Poisonous Cloud";
    /** @var int */
    public $maxLevel = 3;

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $radius = $level * 3;
        foreach ($player->getLevel()->getEntities() as $entity) {
            if ($entity !== $player && $entity instanceof Living && $entity->distance($player) <= $radius) {
                $effect = new EffectInstance(Effect::getEffect(Effect::POISON), $level * 100, $level - 1, false);
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

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}