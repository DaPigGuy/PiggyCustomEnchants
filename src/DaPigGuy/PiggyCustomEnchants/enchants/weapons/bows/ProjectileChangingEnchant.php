<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;
use ReflectionException;

/**
 * Class ProjectileChangingEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows
 */
class ProjectileChangingEnchant extends ReactiveEnchantment
{
    /** @var string */
    private $projectileType;

    /**
     * ProjectileChangingEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param string $name
     * @param string $projectileType
     * @param int $maxLevel
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, string $projectileType, int $maxLevel = 1, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->projectileType = $projectileType;
        $this->maxLevel = $maxLevel;
        parent::__construct($plugin, $id, $rarity);
    }

    /**
     * @return array
     */
    public function getReagent(): array
    {
        return [EntityShootBowEvent::class];
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
        if ($event instanceof EntityShootBowEvent) {
            /** @var Projectile $projectile */
            $projectile = $event->getProjectile();
            ProjectileTracker::removeProjectile($projectile);
            $nbt = Entity::createBaseNBT($projectile, $projectile->getMotion(), $projectile->yaw, $projectile->pitch);
            $projectile = Entity::createEntity($this->projectileType, $player->getLevel(), $nbt, $player, $projectile instanceof Arrow && $this->projectileType === "HomingArrow" ? $projectile->isCritical() : $level, $level);
            $projectile->spawnToAll();
            $event->setProjectile($projectile);
            ProjectileTracker::addProjectile($projectile, $item);
        }
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 2;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return CustomEnchant::ITEM_TYPE_BOW;
    }
}