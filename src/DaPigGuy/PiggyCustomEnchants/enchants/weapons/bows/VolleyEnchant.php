<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class VolleyEnchant extends ReactiveEnchantment
{
    public string $name = "Volley";
    public int $rarity = Rarity::UNCOMMON;

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

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
            $pitch = ($player->getLocation()->pitch + 90) * M_PI / 180;
            $yaw = ($player->getLocation()->yaw + 90 - 45 / 2) * M_PI / 180;
            /** @var Projectile $projectile */
            $projectile = $event->getProjectile();
            for ($i = 0; $i < $amount; $i++) {
                $newProjectile = Utils::createNewProjectile(get_class($projectile), Location::fromObject($player->getEyePos(), $player->getWorld()), $player, $projectile);
                $newDirection = new Vector3(sin($pitch) * cos($yaw + $anglesBetweenArrows * $i), cos($pitch), sin($pitch) * sin($yaw + $anglesBetweenArrows * $i));
                $newProjectile->setMotion($newDirection->normalize()->multiply($projectile->getMotion()->multiply($event->getForce())->length()));
                if ($newProjectile instanceof Arrow) $newProjectile->setPickupMode(Arrow::PICKUP_NONE);
                if ($projectile->isOnFire()) $newProjectile->setOnFire($projectile->getFireTicks() / 20);
                $newProjectile->spawnToAll();
                ProjectileTracker::addProjectile($newProjectile, $item);
            }
            ProjectileTracker::removeProjectile($projectile);
            $projectile->close();
        }
    }
}