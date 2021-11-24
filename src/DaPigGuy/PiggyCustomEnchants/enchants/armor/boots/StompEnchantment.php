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

class StompEnchantment extends ReactiveEnchantment
{
    public string $name = "Stomp";
    public int $rarity = CustomEnchant::RARITY_UNCOMMON;
    public int $maxLevel = 1;

    public int $usageType = CustomEnchant::TYPE_BOOTS;
    public int $itemType = CustomEnchant::ITEM_TYPE_BOOTS;

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
                $entities = $player->getLevel()->getNearbyEntities($player->getBoundingBox());
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
}