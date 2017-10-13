<?php

namespace PiggyCustomEnchants\Entities;


use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

/**
 * Class PigProjectile
 * @package PiggyCustomEnchants\Entities
 */
class PigProjectile extends Projectile
{
    private $porklevel = 1;

    public $width = 0.5;
    public $height = 0.5;
    protected $gravity = 0.05;
    protected $drag = 0.01;
    protected $damage = 1.5;
    private $zombie = false;

    const NETWORK_ID = 12;

    /**
     * PigProjectile constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     * @param Entity|null $shootingEntity
     * @param int $porklevel
     */
    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, $porklevel = 1)
    {
        parent::__construct($level, $nbt, $shootingEntity);
        if ($porklevel < 1) {
            $porklevel = 1;
        }
        if ($porklevel > 6) {
            $porklevel = 6;
        }
        switch ($porklevel) {
            case 1:
                $this->damage = 1.5;
                break;
            case 2:
                $this->damage = 2;
                break;
            case 3:
                $this->damage = 3;
                break;
            case 4: //Secret
                $this->damage = 3;
                $this->setNameTag("Dinnerbone");
                break;
            case 5: //Secret
                $this->damage = 5;
                $this->zombie = true;
                break;
            case 6: //Secret
                $this->damage = 5;

                $this->setNameTag("Dinnerbone");
                $this->zombie = true;
                break;
        }
        $this->porklevel = $porklevel;
    }

    /**
     * @param $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick) : bool
    {
        $hasUpdate = parent::onUpdate($currentTick);
        if (!$this->hadCollision) {
            switch ($this->porklevel) {
                case 2:
                    $this->getLevel()->dropItem($this, Item::get(Item::RAW_PORKCHOP, 0, 1)->setCustomName("Mysterious Raw Pork"));
                    break;
                case 3:
                case 4:
                    $this->getLevel()->dropItem($this, Item::get(Item::COOKED_PORKCHOP, 0, 1)->setCustomName("Mysterious Cooked Pork"));
                    break;
                case 5:
                case 6:
                    $this->getLevel()->dropItem($this, Item::get(Item::ROTTEN_FLESH, 0, 1)->setCustomName("Mysterious Rotten Pork"));
                    break;
            }
        } else {
            $this->close();
        }
        return $hasUpdate;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player)
    {
        $pk = new AddEntityPacket();
        if ($this->zombie) {
            $pk->type = 36;
        } else {
            $pk->type = PigProjectile::NETWORK_ID;
        }
        $pk->entityRuntimeId = $this->getId();
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

    /**
     * @return int
     */
    public function getPorkLevel()
    {
        return $this->porklevel;
    }
}