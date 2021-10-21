<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class GrapplingEnchant extends ReactiveEnchantment
{
    public string $name = "Grappling";
    public int $maxLevel = 1;

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class, ProjectileHitBlockEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $projectile = $event->getChild();
            $task = new ClosureTask(function () use ($event, $projectile): void {
                if ($projectile instanceof Projectile) {
                    $damagerPosition = $event->getDamager()->getPosition();
                    $entity = $event->getEntity();
                    $entityPosition = $entity->getPosition();
                    $distance = $damagerPosition->distance($entityPosition);
                    if ($distance > 0) {
                        $motionX = (1.0 + 0.07 * $distance) * ($damagerPosition->x - $entityPosition->x) / $distance;
                        $motionY = (1.0 + 0.03 * $distance) * ($damagerPosition->y - $entityPosition->y) / $distance - 0.5 * -0.08 * $distance;
                        $motionZ = (1.0 + 0.07 * $distance) * ($damagerPosition->z - $entityPosition->z) / $distance;
                        $entity->setMotion(new Vector3($motionX, $motionY, $motionZ));
                    }
                }
            });
            $this->plugin->getScheduler()->scheduleDelayedTask($task, 1);
            Utils::setShouldTakeFallDamage($player, false);
        }
        if ($event instanceof ProjectileHitBlockEvent) {
            $projectilePosition = $event->getEntity()->getPosition();
            $shooter = $event->getEntity()->getOwningEntity();
            $shooterPosition = $shooter->getPosition();
            $distance = $projectilePosition->distance($shooterPosition);
            if ($distance < 6) {
                if ($projectilePosition->y > $shooterPosition->y) {
                    $shooter->setMotion(new Vector3(0, 0.25, 0));
                } else {
                    $v = $projectilePosition->subtractVector($shooterPosition);
                    $shooter->setMotion($v);
                }
            } else {
                $motionX = (1.0 + 0.07 * $distance) * ($projectilePosition->x - $shooterPosition->x) / $distance;
                $motionY = (1.0 + 0.03 * $distance) * ($projectilePosition->y - $shooterPosition->y) / $distance - 0.5 * -0.08 * $distance;
                $motionZ = (1.0 + 0.07 * $distance) * ($projectilePosition->z - $shooterPosition->z) / $distance;
                $shooter->setMotion(new Vector3($motionX, $motionY, $motionZ));
            }
            Utils::setShouldTakeFallDamage($player, false);
        }
    }
}