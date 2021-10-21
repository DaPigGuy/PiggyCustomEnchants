<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\traits\TickingTrait;
use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\EnchantmentTableParticle;

class ForcefieldEnchant extends ToggleableEnchantment
{
    use TickingTrait;

    public string $name = "Forcefield";
    public int $rarity = Rarity::MYTHIC;

    public int $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    public int $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getDefaultExtraData(): array
    {
        return ["radiusMultiplier" => 0.75];
    }

    public function tick(Player $player, Item $item, Inventory $inventory, int $slot, int $level): void
    {
        $forcefieldLevel = $this->getStack($player);
        if ($forcefieldLevel > 0) {
            $radius = $forcefieldLevel * $this->extraData["radiusMultiplier"];
            $entities = $player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius), $player);
            foreach ($entities as $entity) {
                if ($entity instanceof Projectile) {
                    if ($entity->getOwningEntity() !== $player) $entity->setMotion($entity->getMotion()->multiply(-1));
                } else {
                    if (!$entity instanceof ItemEntity && !$entity instanceof ExperienceOrb && !AllyChecks::isAlly($player, $entity)) {
                        $entity->setMotion(new Vector3($player->getPosition()->subtractVector($entity->getPosition())->normalize()->multiply(-0.75)->x, 0, $player->getPosition()->subtractVector($entity->getPosition())->normalize()->multiply(-0.75)->z));
                    }
                }
            }
            if ($player->getServer()->getTick() % 5 === 0) {
                $diff = $radius / $forcefieldLevel;
                for ($theta = 0; $theta <= 360; $theta += $diff) {
                    $player->getWorld()->addParticle($player->getPosition()->add($radius * sin($theta), 0.5, $radius * cos($theta)), new EnchantmentTableParticle());
                }
            }
        }
    }
}