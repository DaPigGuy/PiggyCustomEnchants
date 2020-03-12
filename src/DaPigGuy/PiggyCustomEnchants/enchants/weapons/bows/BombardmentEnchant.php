<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

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

            $nbt = Entity::createBaseNBT($entity->add(0, 255 - $entity->y), new Vector3(0, -5));
            $nbt->setInt("TileID", Block::TNT);
            $nbt->setInt("Bombardment", $level);

            /** @var FallingBlock $entity */
            $entity = Entity::createEntity("FallingSand", $player->getLevel(), $nbt);
            $entity->setOwningEntity($player);
            $entity->spawnToAll();
        }
    }
}