<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;

class PiggyExplosion extends Explosion
{
    public function __construct(Position $center, float $radius, protected Player $player, protected bool $entityDamage = true)
    {
        parent::__construct($center, $radius, $this->player);
    }

    public function explodeB(): bool
    {
        $source = (new Vector3($this->source->x, $this->source->y, $this->source->z))->floor();
        $yield = (1 / $this->radius) * 100;

        $ev = new EntityExplodeEvent($this->player, $this->source, $this->affectedBlocks, $yield);
        $ev->call();
        if ($ev->isCancelled()) {
            return false;
        } else {
            $this->affectedBlocks = $ev->getBlockList();
        }

        $explosionSize = $this->radius * 2;
        $minX = (int)floor($this->source->x - $explosionSize - 1);
        $maxX = (int)ceil($this->source->x + $explosionSize + 1);
        $minY = (int)floor($this->source->y - $explosionSize - 1);
        $maxY = (int)ceil($this->source->y + $explosionSize + 1);
        $minZ = (int)floor($this->source->z - $explosionSize - 1);
        $maxZ = (int)ceil($this->source->z + $explosionSize + 1);

        $explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

        $list = $this->world->getNearbyEntities($explosionBB, $this->player);
        foreach ($list as $entity) {
            $entityPos = $entity->getPosition();
            $distance = $entityPos->distance($this->source) / $explosionSize;

            if ($distance <= 1 && $this->entityDamage) {
                $motion = $entityPos->subtractVector($this->source)->normalize();
                $impact = (1 - $distance);
                $damage = (int)((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

                $ev = new EntityDamageByEntityEvent($this->player, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
                $entity->attack($ev);
                $entity->setMotion($motion->multiply($impact));
            }
        }

        $airBlock = VanillaBlocks::AIR();

        $item = $this->player->getInventory()->getItemInHand();
        RecursiveEnchant::$isUsing[$this->player->getName()] = true;
        foreach ($this->affectedBlocks as $key => $block) {
            $ev = new BlockBreakEvent($this->player, $block, $item, true, $block->getDrops($item));
            $ev->call();
            if ($ev->isCancelled()) {
                unset($this->affectedBlocks[$key]);
                continue;
            }
            $pos = $block->getPosition();
            if ($block instanceof TNT) {
                $block->ignite(mt_rand(10, 30));
            } else {
                foreach ($ev->getDrops() as $drop) {
                    $this->world->dropItem($pos->add(0.5, 0.5, 0.5), $drop);
                }
                if (($t = $this->world->getTileAt((int)$pos->x, (int)$pos->y, (int)$pos->z)) !== null) {
                    $t->onBlockDestroyed(); //needed to create drops for inventories
                }
                $this->world->setBlockAt((int)$pos->x, (int)$pos->y, (int)$pos->z, $airBlock, false);
            }
        }
        unset(RecursiveEnchant::$isUsing[$this->player->getName()]);

        $this->world->addParticle($source, new HugeExplodeSeedParticle());
        $this->world->addSound($source, new ExplodeSound());
        return true;
    }
}