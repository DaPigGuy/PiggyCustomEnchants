<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use pocketmine\data\bedrock\LegacyEntityIdToStringIdMap;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class PigProjectile extends PiggyProjectile
{
    const PORK_LEVELS = [
        //level => [damage, dinnerbone, zombie, drop id, drop name]
        1 => [1, false, false, ItemIds::AIR, ""],
        2 => [2, false, false, ItemIds::RAW_PORKCHOP, "Mysterious Raw Pork"],
        3 => [2, false, false, ItemIds::COOKED_PORKCHOP, "Mysterious Cooked Pork"],
        4 => [3, true, false, ItemIds::COOKED_PORKCHOP, "Mysterious Cooked Pork"],
        5 => [5, false, true, ItemIds::ROTTEN_FLESH, "Mysterious Rotten Pork"],
        6 => [6, true, true, ItemIds::ROTTEN_FLESH, "Mysterious Rotten Pork"]
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
    private $porkLevel;
    /** @var bool */
    private $zombie;

    public function __construct(World $level, CompoundTag $nbt, Entity $shootingEntity = null, int $porkLevel = 1)
    {
        parent::__construct($level, $nbt, $shootingEntity);
        if ($porkLevel < 1) {
            $porkLevel = 1;
        }
        if ($porkLevel > 6) {
            $porkLevel = 6;
        }
        $values = self::PORK_LEVELS[$porkLevel];
        $this->damage = $values[0];
        if ($values[1]) {
            $this->setNameTag("Dinnerbone");
        }
        $this->porkLevel = $porkLevel;
        $this->zombie = $values[2];
    }

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
                    if (!$drop->isNull()) {
                        $nbt = EntityFactory::createBaseNBT($this->getPosition(), $motion, lcg_value() * 360, 0);
                        $nbt->setTag("Item", $drop->nbtSerialize());
                        $nbt->setShort("Health", 5);
                        $nbt->setShort("PickupDelay", 10);
                        $nbt->setShort("Age", 5700);
                        $itemEntity = EntityFactory::getInstance()->create(ItemEntity::class, $this->location->world, $nbt);
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

    public function getPorkLevel(): int
    {
        return $this->porkLevel;
    }

    public function isZombie(): bool
    {
        return $this->zombie;
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        $values = self::PORK_LEVELS[$this->getPorkLevel()];
        return [
            ItemFactory::getInstance()->get($values[3], 0, 1)->setCustomName(TextFormat::RESET . TextFormat::WHITE . $values[4])
        ];
    }

    protected function sendSpawnPacket(Player $player): void
    {
        $pk = new AddActorPacket();
        $pk->type = LegacyEntityIdToStringIdMap::getInstance()->legacyToString($this->isZombie() ? EntityLegacyIds::ZOMBIE_PIGMAN : EntityLegacyIds::PIG);
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->getPosition();
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->getSyncedNetworkData(false);
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public static function getNetworkTypeId(): int
    {
        return EntityLegacyIds::PIG;
    }
}