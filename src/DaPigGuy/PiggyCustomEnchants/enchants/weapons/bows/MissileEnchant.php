<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Class MissileEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows
 */
class MissileEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Missile";


    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [ProjectileHitBlockEvent::class];
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
        if ($event instanceof ProjectileHitBlockEvent) {
            $projectile = $event->getEntity();
            for ($i = 0; $i <= $level; $i++) {
                /** @var PiggyTNT $tnt */
                $tnt = EntityFactory::create(PiggyTNT::class, $projectile->getWorld(), EntityFactory::createBaseNBT($projectile->getPosition())->setShort("Fuse", 40));
                $tnt->worldDamage = $this->plugin->getConfig()->getNested("world-damage.missile", false);
                $tnt->setOwningEntity($player);
                $tnt->spawnToAll();
                $projectile->flagForDespawn();
            }
        }
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return self::ITEM_TYPE_BOW;
    }
}