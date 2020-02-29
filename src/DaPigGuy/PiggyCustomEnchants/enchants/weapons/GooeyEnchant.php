<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;

/**
 * Class GooeyEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons
 */
class GooeyEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Gooey";

    /**
     * @return array
     */
    public function getDefaultExtraData(): array
    {
        return ["base" => 0.75, "multiplier" => 0.15];
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param Event $event
     * @param int $level
     * @param int $stack
     */
    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $entity = $event->getEntity();
            $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity, $level): void {
                if (!$entity->isClosed() && !$entity->isFlaggedForDespawn()) $entity->setMotion(new Vector3($entity->getMotion()->x, $level * $this->extraData["multiplier"] + $this->extraData["base"], $entity->getMotion()->z));
            }), 1);
        }
    }
}