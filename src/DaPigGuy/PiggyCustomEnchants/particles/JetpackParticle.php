<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\particles;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\world\particle\Particle;

class JetpackParticle implements Particle
{
    /**
     * @return LevelEventPacket
     */
    public function encode(Vector3 $pos)
    {
        return LevelEventPacket::standardParticle(ParticleIds::CAMPFIRE_SMOKE, 0, $pos);
    }
}