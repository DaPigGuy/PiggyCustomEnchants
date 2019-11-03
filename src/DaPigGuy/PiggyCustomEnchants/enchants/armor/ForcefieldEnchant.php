<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\EnchantmentTableParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Class ForcefieldEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class ForcefieldEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    /** @var string */
    public $name = "Forcefield";

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     */
    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $forcefieldLevel = $this->stack[$player->getName()];
        if ($forcefieldLevel > 0) {
            $radius = $forcefieldLevel * 0.75;
            $entities = $player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius), $player);
            foreach ($entities as $entity) {
                if ($entity instanceof Projectile) {
                    if ($entity->getOwningEntity() !== $player) {
                        $entity->setMotion($entity->getMotion()->multiply(-1));
                    }
                } else {
                    if (!$entity instanceof ItemEntity && !$entity instanceof ExperienceOrb && !isset($entity->namedtag->getValue()["SlapperVersion"])) {
                        $entity->setMotion(new Vector3($player->subtract($entity)->normalize()->multiply(-0.75)->x, 0, $player->subtract($entity)->normalize()->multiply(-0.75)->z));
                    }
                }
            }
            if ($player->getServer()->getTick() % 5 === 0) {
                $diff = $radius / $forcefieldLevel;
                for ($theta = 0; $theta <= 360; $theta += $diff) {
                    $pos = $player->add($radius * sin($theta), 0.5, $radius * cos($theta));
                    $player->getLevel()->addParticle(new EnchantmentTableParticle($pos));
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