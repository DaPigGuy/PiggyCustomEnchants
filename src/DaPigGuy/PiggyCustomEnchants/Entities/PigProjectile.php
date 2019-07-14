<?php

namespace DaPigGuy\PiggyCustomEnchants\Entities;

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\Player;

/**
 * Class PigProjectile
 * @package DaPigGuy\PiggyCustomEnchants\Entities
 */
class PigProjectile extends PiggyProjectile
{
    /**
     * Used to replace const NETWORK_ID to resolve registration conflicts with vanilla entities
     * @var int
     */
    const TYPE_ID = 12;
    const PORKLEVELS = [
        //level => [damage, dinnerbone, zombie, drop id, drop name]
        1 => [1, false, false, Item::AIR, ""],
        2 => [2, false, false, Item::RAW_PORKCHOP, "Mysterious Raw Pork"],
        3 => [2, false, false, Item::COOKED_PORKCHOP, "Mysterious Cooked Pork"],
        4 => [3, true, false, Item::COOKED_PORKCHOP, "Mysterious Cooked Pork"],
        5 => [5, false, true, Item::ROTTEN_FLESH, "Mysterious Rotten Pork"],
        6 => [6, true, true, Item::ROTTEN_FLESH, "Mysterious Rotten Pork"]
    ];
    /** @var float */
    public $width = 0.9;
    /** @var float */
    public $height = 0.9;

    /** @var float */
    protected $drag = 0.01;
    /** @var float */
    protected $gravity = 0.05;

    /** @var float */
    protected $damage = 1.5;
    /** @var int */
    private $porklevel = 1;
    /** @var bool */
    private $zombie = false;

    /**
     * PigProjectile constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param Entity|null $shootingEntity
     * @param bool $placeholder
     * @param int $porklevel
     */
    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $placeholder = false, int $porklevel = 1)
    {
        if ($porklevel < 1) {
            $porklevel = 1;
        }
        if ($porklevel > 6) {
            $porklevel = 6;
        }
        $values = self::PORKLEVELS[$porklevel];
        $this->damage = $values[0];
        if ($values[1]) {
            $this->setNameTag("Dinnerbone");
        }
        $this->zombie = $values[2];
        $this->porklevel = $porklevel;
        parent::__construct($level, $nbt, $shootingEntity, $placeholder);
    }

    /**
     * @param int $tickDiff
     * @return bool
     * @internal param $currentTick
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isCollided) {
            if ($this->getPorkLevel() > 1) {
                foreach ($this->getDrops() as $drop) {
                    $motion = new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1);
                    $itemTag = $drop->nbtSerialize();
                    $itemTag->setName("Item");
                    if (!$drop->isNull()) {
                        $nbt = Entity::createBaseNBT($this, $motion, lcg_value() * 360, 0);
                        $nbt->setShort("Health", 5);
                        $nbt->setShort("PickupDelay", 10);
                        $nbt->setShort("Age", 5700);
                        $nbt->setTag($itemTag);
                        $itemEntity = Entity::createEntity("Item", $this->level, $nbt);
                        if ($itemEntity instanceof ItemEntity) {
                            $itemEntity->spawnToAll();
                        }
                    }
                }
            }
        } else {
            $this->flagForDespawn();
            $hasUpdate = true;
        }
        return $hasUpdate;
    }

    /**
     * @return int
     */
    public function getPorkLevel()
    {
        return $this->porklevel;
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        $values = self::PORKLEVELS[$this->getPorkLevel()];
        return [
            Item::get($values[3], 0, 1)->setCustomName($values[4])
        ];
    }

    /**
     * @param Player $player
     */
    protected function sendSpawnPacket(Player $player): void
    {
        $pk = new AddActorPacket();
        $pk->type = $this->isZombie() ? Entity::ZOMBIE_PIGMAN : static::TYPE_ID;
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->propertyManager->getAll();
        $player->sendDataPacket($pk);
    }

    /**
     * @return bool
     */
    public function isZombie()
    {
        return $this->zombie;
    }
}