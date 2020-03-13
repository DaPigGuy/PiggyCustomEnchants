<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\BombardmentTNT;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class BombardmentEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Bombardment";

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $entity = $event->getEntity();

            $nbt = EntityFactory::createBaseNBT($entity->getPosition()->add(0, 255 - $entity->getPosition()->y), new Vector3(0, -5));
            $nbt->setInt("TileID", BlockLegacyIds::TNT);
            $nbt->setInt("Level", $level);

            /** @var BombardmentTNT $entity */
            $entity = EntityFactory::create(BombardmentTNT::class, $player->getWorld(), $nbt);
            $entity->setOwningEntity($player);
            $entity->spawnToAll();
        }
    }
}