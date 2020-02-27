<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\particles;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\world\particle\Particle;

/**
 * Class JetpackParticle
 * @package DaPigGuy\PiggyCustomEnchants\particles
 */
class JetpackParticle implements Particle
{
    /**
     * @param Vector3 $pos
     * @return ClientboundPacket|ClientboundPacket[]|void
     */
    public function encode(Vector3 $pos)
    {
        return LevelEventPacket::standardParticle(ParticleIds::CAMPFIRE_SMOKE, 0, $pos);
    }
}