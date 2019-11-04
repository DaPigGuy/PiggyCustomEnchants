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

/**
 * Class BombardmentEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows
 */
class BombardmentEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Bombardment";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
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

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOW;
    }
}