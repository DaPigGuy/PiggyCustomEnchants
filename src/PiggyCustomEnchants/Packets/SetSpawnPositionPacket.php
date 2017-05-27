<?php

namespace PiggyCustomEnchants\Packets;

/**
 * Temporary until MCPE 1.1 changes are pushed to master
 *
 * Class SetSpawnPositionPacket
 * @package PiggyCustomEnchants\Packets
 */
class SetSpawnPositionPacket extends \pocketmine\network\mcpe\protocol\SetSpawnPositionPacket
{
    const TYPE_PLAYER_SPAWN = 0;
    const TYPE_WORLD_SPAWN = 1;

    public $spawnType;
    public $spawnForced;

    public function decode()
    {
        $this->spawnType = $this->getVarInt();
        $this->getBlockPosition($this->x, $this->y, $this->z);
        $this->spawnForced = $this->getBool();
    }

    public function encode()
    {
        $this->reset();
        $this->putVarInt($this->spawnType);
        $this->putBlockPosition($this->x, $this->y, $this->z);
        $this->putBool($this->spawnForced);
    }
}