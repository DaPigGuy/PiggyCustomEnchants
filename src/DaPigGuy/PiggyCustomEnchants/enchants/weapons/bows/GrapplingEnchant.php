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
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

class GrapplingEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Grappling";
    /** @var int */
    public $maxLevel = 1;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

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
                    $damager = $event->getDamager();
                    $entity = $event->getEntity();
                    $distance = $damager->distance($entity);
                    if ($distance > 0) {
                        $motionX = (1.0 + 0.07 * $distance) * ($damager->x - $entity->x) / $distance;
                        $motionY = (1.0 + 0.03 * $distance) * ($damager->y - $entity->y) / $distance - 0.5 * -0.08 * $distance;
                        $motionZ = (1.0 + 0.07 * $distance) * ($damager->z - $entity->z) / $distance;
                        $entity->setMotion(new Vector3($motionX, $motionY, $motionZ));
                    }
                }
            });
            $this->plugin->getScheduler()->scheduleDelayedTask($task, 1);
            Utils::setShouldTakeFallDamage($player, false);
        }
        if ($event instanceof ProjectileHitBlockEvent) {
            $projectile = $event->getEntity();
            $shooter = $projectile->getOwningEntity();
            $distance = $projectile->distance($shooter);
            if ($distance < 6) {
                if ($projectile->y > $shooter->y) {
                    $shooter->setMotion(new Vector3(0, 0.25, 0));
                } else {
                    $v = $projectile->subtract($shooter);
                    $shooter->setMotion($v);
                }
            } else {
                $motionX = (1.0 + 0.07 * $distance) * ($projectile->x - $shooter->x) / $distance;
                $motionY = (1.0 + 0.03 * $distance) * ($projectile->y - $shooter->y) / $distance - 0.5 * -0.08 * $distance;
                $motionZ = (1.0 + 0.07 * $distance) * ($projectile->z - $shooter->z) / $distance;
                $shooter->setMotion(new Vector3($motionX, $motionY, $motionZ));
            }
            Utils::setShouldTakeFallDamage($player, false);
        }
    }
}