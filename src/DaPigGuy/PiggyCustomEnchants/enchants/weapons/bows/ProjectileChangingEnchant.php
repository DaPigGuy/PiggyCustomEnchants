<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ProjectileChangingEnchant extends ReactiveEnchantment
{
    /**
     * @var string
     * @phpstan-var class-string<Entity>
     */
    private $projectileType;

    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_BOW;

    /**
     * @phpstan-param class-string<Entity> $projectileType
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, string $name, string $projectileType, int $maxLevel = 1, int $rarity = self::RARITY_RARE)
    {
        $this->name = $name;
        $this->rarity = $rarity;
        $this->projectileType = $projectileType;
        $this->maxLevel = $maxLevel;
        parent::__construct($plugin, $id);
    }

    public function getReagent(): array
    {
        return [EntityShootBowEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityShootBowEvent) {
            /** @var Projectile $projectile */
            $projectile = $event->getProjectile();
            ProjectileTracker::removeProjectile($projectile);

            $newProjectile = Utils::createNewProjectile($this->projectileType, $projectile->getLocation(), $player, $projectile, $level);
            $newProjectile->setMotion($projectile->getMotion());
            $newProjectile->spawnToAll();

            $event->setProjectile($newProjectile);
            ProjectileTracker::addProjectile($newProjectile, $item);
        }
    }

    public function getPriority(): int
    {
        return 2;
    }
}