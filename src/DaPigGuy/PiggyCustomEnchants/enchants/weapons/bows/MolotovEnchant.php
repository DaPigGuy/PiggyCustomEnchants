<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class MolotovEnchant extends ReactiveEnchantment
{
    public string $name = "Molotov";
    public int $rarity = Rarity::UNCOMMON;

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

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
                    $fire = new FallingBlock(Location::fromObject($entity->getLocation()->add(0.5, 1, 0.5), $entity->getWorld()), VanillaBlocks::FIRE());
                    $fire->setMotion(new Vector3($x, 0.1, $z));
                    $fire->setOnFire(1638); //Falling Sand with block id of fire not rendered by game
                    $fire->spawnToAll();
                }
            }
        }
    }
}