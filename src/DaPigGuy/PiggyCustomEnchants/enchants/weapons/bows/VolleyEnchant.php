<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\HomingArrow;
use DaPigGuy\PiggyCustomEnchants\entities\PigProjectile;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class VolleyEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Volley";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityShootBowEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["base" => 1, "multiplier" => 2];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityShootBowEvent) {
            $amount = $this->extraData["base"] + $this->extraData["multiplier"] * $level;
            $anglesBetweenArrows = (45 / ($amount - 1)) * M_PI / 180;
            $pitch = ($player->pitch + 90) * M_PI / 180;
            $yaw = ($player->yaw + 90 - 45 / 2) * M_PI / 180;
            /** @var Projectile $projectile */
            $projectile = $event->getProjectile();
            for ($i = 0; $i < $amount; $i++) {
                $class = get_class($projectile);
                $entityType = substr($class, strrpos($class, "\\") + 1);

                $nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight()), $player->getDirectionVector(), $player->yaw, $player->pitch);
                /** @var Projectile $newProjectile */
                $newProjectile = Entity::createEntity($entityType, $player->getLevel(), $nbt, $player, ($projectile instanceof Arrow ? $projectile->isCritical() : ($projectile instanceof PigProjectile ? $projectile->getPorkLevel() : null)), ($projectile instanceof HomingArrow ? $projectile->getEnchantmentLevel() : null));
                if ($newProjectile instanceof Arrow) $newProjectile->setPickupMode(Arrow::PICKUP_NONE);
                $newProjectile->spawnToAll();
                ProjectileTracker::addProjectile($newProjectile, $item);

                $newDirection = new Vector3(sin($pitch) * cos($yaw + $anglesBetweenArrows * $i), cos($pitch), sin($pitch) * sin($yaw + $anglesBetweenArrows * $i));
                $newProjectile->setMotion($newDirection->normalize()->multiply($projectile->getMotion()->multiply($event->getForce())->length()));
                if ($projectile->isOnFire()) $newProjectile->setOnFire($projectile->getFireTicks() / 20);
            }
            ProjectileTracker::removeProjectile($projectile);
            $projectile->close();
        }
    }
}