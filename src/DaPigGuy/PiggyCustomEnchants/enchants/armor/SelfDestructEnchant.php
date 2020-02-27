<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use pocketmine\entity\EntityFactory;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;

/**
 * Class SelfDestructEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class SelfDestructEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Self Destruct";

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [PlayerDeathEvent::class];
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
        if ($event instanceof PlayerDeathEvent) {
            for ($i = 0; $i < $level; $i++) {
                $random = new Random();
                /** @var PiggyTNT $tnt */
                $tnt = EntityFactory::create(PiggyTNT::class, $player->getWorld(), EntityFactory::createBaseNBT($player->getPosition(), new Vector3($random->nextFloat() * 1.5 - 1, $random->nextFloat() * 1.5, $random->nextFloat() * 1.5 - 1),)->setShort("Fuse", 40));
                $tnt->worldDamage = $this->plugin->getConfig()->getNested("world-damage.self-destruct", false);
                $tnt->setOwningEntity($player);
                $tnt->spawnToAll();
            }
        }
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_ARMOR;
    }
}