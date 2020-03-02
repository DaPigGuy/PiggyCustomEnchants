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
use pocketmine\player\Player;

class StompEnchantment extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Stomp";
    /** @var int */
    public $maxLevel = 1;

    public function getReagent(): array
    {
        return [EntityDamageEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["redistributedDamageMultiplier" => 0.5, "absorbedDamageMultiplier" => 0.75];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageEvent) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $entities = $player->getWorld()->getNearbyEntities($player->getBoundingBox());
                foreach ($entities as $entity) {
                    if ($player === $entity) {
                        continue;
                    }
                    $ev = new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $event->getFinalDamage() * $this->extraData["redistributedDamageMultiplier"]);
                    $entity->attack($ev);
                }
                $event->setModifier(-($event->getFinalDamage() * $this->extraData["absorbedDamageMultiplier"] * count($entities)), CustomEnchantIds::STOMP);
            }
        }
    }

    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_BOOTS;
    }

    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOOTS;
    }
}