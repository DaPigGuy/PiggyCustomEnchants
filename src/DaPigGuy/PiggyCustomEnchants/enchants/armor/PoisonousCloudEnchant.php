<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\TickingEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Color;
use pocketmine\world\particle\DustParticle;

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
        foreach ($player->getWorld()->getEntities() as $entity) {
            if ($entity !== $player && $entity instanceof Living && !AllyChecks::isAlly($player, $entity) && $entity->getPosition()->distance($player->getPosition()) <= $radius) {
                $effect = new EffectInstance(VanillaEffects::POISON(), $level * 100, $level - 1, false);
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