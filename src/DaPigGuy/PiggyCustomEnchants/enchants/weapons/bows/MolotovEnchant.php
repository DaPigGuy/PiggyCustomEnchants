<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class MolotovEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Molotov";
    /** @var int */
    public $rarity = CustomEnchant::RARITY_UNCOMMON;

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
            $boundaries = 0.1 * $level;
            for ($x = $boundaries; $x >= -$boundaries; $x -= 0.1) {
                for ($z = $boundaries; $z >= -$boundaries; $z -= 0.1) {
                    $nbt = Entity::createBaseNBT($entity->add(0.5, 1, 0.5), new Vector3($x, 0.1, $z));
                    $nbt->setInt("TileID", Block::FIRE);
                    $nbt->setByte("Data", 0);
                    $fire = Entity::createEntity("FallingSand", $entity->getLevel(), $nbt);
                    $fire->setOnFire(1638); //Falling Sand with block id of fire not rendered by game
                    $fire->spawnToAll();
                }
            }
        }
    }
}