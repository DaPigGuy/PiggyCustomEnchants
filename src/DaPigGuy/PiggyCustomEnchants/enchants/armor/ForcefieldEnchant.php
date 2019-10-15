<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use ReflectionException;

/**
 * Class ForcefieldEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants\armor
 */
class ForcefieldEnchant extends ToggleableEnchantment
{
    /** @var string */
    public $name = "Forcefield";

    /** @var TaskHandler */
    private $taskHandler;

    /** @var Player[] */
    public static $forcefield = [];
    /** @var array */
    public static $forcefieldLevel;

    /**
     * CustomEnchant constructor.
     * @param Main $plugin
     * @param int $id
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(Main $plugin, int $id, int $rarity = self::RARITY_RARE)
    {
        parent::__construct($plugin, $id, $rarity);
        $this->taskHandler = $plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            foreach (self::$forcefield as $player) {
                $forcefieldLevel = self::$forcefieldLevel[$player->getName()];
                if ($forcefieldLevel > 0) {
                    $radius = $forcefieldLevel * 0.75;
                    $entities = $player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius), $player);
                    foreach ($entities as $entity) {
                        if ($entity instanceof Projectile) {
                            if ($entity->getOwningEntity() !== $player) {
                                $entity->setMotion($entity->getMotion()->multiply(-1));
                            }
                        } else {
                            if (!$entity instanceof ItemEntity && !isset($entity->namedtag->getValue()["SlapperVersion"])) {
                                $entity->setMotion(new Vector3($player->subtract($entity)->normalize()->multiply(-0.75)->x, 0, $player->subtract($entity)->normalize()->multiply(-0.75)->z));
                            }
                        }
                    }
                    if ($currentTick % 5 === 0) {
                        $diff = $radius / $forcefieldLevel;
                        for ($theta = 0; $theta <= 360; $theta += $diff) {
                            $pos = $player->add($radius * sin($theta), 0.5, $radius * cos($theta));
                            $player->getLevel()->addParticle(new FlameParticle($pos));
                        }
                    }
                }
            }
        }), 1);
    }

    /**
     * @param Player $player
     * @param Item $item
     * @param Inventory $inventory
     * @param int $slot
     * @param int $level
     * @param bool $toggle
     */
    public function toggle(Player $player, Item $item, Inventory $inventory, int $slot, int $level, bool $toggle)
    {
        self::$forcefield[$player->getName()] = $player;
        self::$forcefieldLevel[$player->getName()] = (self::$forcefieldLevel[$player->getName()] ?? 0) + ($toggle ? $level : -$level);
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

    public function unregister(): void
    {
        $this->taskHandler->cancel();
    }
}