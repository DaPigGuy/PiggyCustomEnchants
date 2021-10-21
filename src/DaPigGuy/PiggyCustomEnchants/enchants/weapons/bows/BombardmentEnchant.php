<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\BombardmentTNT;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class BombardmentEnchant extends ReactiveEnchantment
{
    public string $name = "Bombardment";

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $entity = $event->getEntity();
            $bombardmentEntity = new BombardmentTNT(Location::fromObject($entity->getLocation()->add(0, 255 - $entity->getLocation()->y, 0), $entity->getWorld()), null, $level);
            $bombardmentEntity->setOwningEntity($player);
            $bombardmentEntity->setMotion(new Vector3(0, -5, 0));
            $bombardmentEntity->spawnToAll();
        }
    }
}