<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class PigProjectile extends PiggyProjectile
{
    /**
     * @const array<int, bool, bool, int, string>
     */
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
    protected $drag = 0.01;
    /** @var float */
    protected $gravity = 0.05;

    /** @var float */
    protected $damage = 1.5;
    private int $porkLevel;
    private bool $zombie;

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null, int $porkLevel = 1)
    {
        parent::__construct($location, $shootingEntity, $nbt);
        $this->porkLevel = max(1, min($porkLevel, 6));
        $values = self::PORK_LEVELS[$this->porkLevel];
        $this->damage = $values[0];
        if ($values[1]) {
            $this->setNameTag("Dinnerbone");
        }
        $this->zombie = $values[2];
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) return false;
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isCollided) {
            if ($this->getPorkLevel() > 1) {
                foreach ($this->getDrops() as $drop) {
                    if (!$drop->isNull()) {
                        $itemEntity = new ItemEntity(Location::fromObject($this->getPosition(), $this->getWorld(), lcg_value() * 360, 0), $drop);
                        $itemEntity->setDespawnDelay(300);
                        $itemEntity->setMotion(new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1));
                        $itemEntity->spawnToAll();
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
        $player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
            $this->getId(), $this->getId(),
            $this->isZombie() ? EntityIds::ZOMBIE_PIGMAN : EntityIds::PIG,
            $this->getPosition()->asVector3(),
            $this->getMotion(),
            $this->location->pitch,
            $this->location->yaw,
            $this->location->yaw,
            array_map(function (Attribute $attr): NetworkAttribute {
                return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue());
            }, $this->attributeMap->getAll()),
            $this->getAllNetworkData(),
            []
        ));
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::PIG;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.9, 0.9);
    }
}