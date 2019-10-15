<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor\boots;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Class StompEnchantment
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor\boots
 */
class StompEnchantment extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Stomp";
    /** @var int */
    public $maxLevel = 1;

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityDamageEvent::class];
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
        if ($event instanceof EntityDamageEvent) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $entities = $player->getLevel()->getNearbyEntities($player->getBoundingBox());
                foreach ($entities as $entity) {
                    if ($player === $entity) {
                        continue;
                    }
                    $ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $event->getFinalDamage() / 2);
                    $entity->attack($ev);
                }
                $event->setModifier(-($event->getFinalDamage() * (3 / 4) * count($entities)), CustomEnchantIds::STOMP);
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_BOOTS;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOOTS;
    }
}