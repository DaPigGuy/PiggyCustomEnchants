<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\World;

class PiggyExplosion extends Explosion
{
    /** @var Player */
    protected $what;

    public function __construct(Position $center, float $size, Player $what)
    {
        parent::__construct($center, $size, $what);
        $this->what = $what;
    }

    public function explodeB(): bool
    {
        $send = [];
        $updateBlocks = [];

        $source = (new Vector3($this->source->x, $this->source->y, $this->source->z))->floor();
        $yield = (1 / $this->size) * 100;

        $ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, $yield);
        $ev->call();
        if ($ev->isCancelled()) {
            return false;
        } else {
            $this->affectedBlocks = $ev->getBlockList();
        }

        $explosionSize = $this->size * 2;
        $minX = (int)floor($this->source->x - $explosionSize - 1);
        $maxX = (int)ceil($this->source->x + $explosionSize + 1);
        $minY = (int)floor($this->source->y - $explosionSize - 1);
        $maxY = (int)ceil($this->source->y + $explosionSize + 1);
        $minZ = (int)floor($this->source->z - $explosionSize - 1);
        $maxZ = (int)ceil($this->source->z + $explosionSize + 1);

        $explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

        /** @var Entity[] $list */
        $list = $this->world->getNearbyEntities($explosionBB, $this->what);
        foreach ($list as $entity) {
            $entityPos = $entity->getPosition();
            $distance = $entityPos->distance($this->source) / $explosionSize;

            if ($distance <= 1) {
                $motion = $entityPos->subtractVector($this->source)->normalize();
                $impact = (1 - $distance) * ($exposure = 1);
                $damage = (int)((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

                $ev = new EntityDamageByEntityEvent($this->what, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
                $entity->attack($ev);
                $entity->setMotion($motion->multiply($impact));
            }
        }

        $airBlock = VanillaBlocks::AIR();

        $item = $this->what->getInventory()->getItemInHand();
        RecursiveEnchant::$isUsing[$this->what->getName()] = true;
        foreach ($this->affectedBlocks as $key => $block) {
            $ev = new BlockBreakEvent($this->what, $block, $item, true, $block->getDrops($item));
            $ev->call();
            if ($ev->isCancelled()) {
                unset($this->affectedBlocks[$key]);
                continue;
            }
            $pos = $block->getPos();
            if ($block instanceof TNT) {
                $block->ignite(mt_rand(10, 30));
            } else {
                foreach ($ev->getDrops() as $drop) {
                    $this->world->dropItem($pos->add(0.5, 0.5, 0.5), $drop);
                }
                if (($t = $this->world->getTileAt((int)$pos->x, (int)$pos->y, (int)$pos->z)) !== null) {
                    $t->onBlockDestroyed(); //needed to create drops for inventories
                }
                $this->world->setBlockAt((int)$pos->x, (int)$pos->y, (int)$pos->z, $airBlock, false); //TODO: should updating really be disabled here?
                $this->world->updateAllLight((int)$pos->x, (int)$pos->y, (int)$pos->z);
            }

            foreach (Facing::ALL as $side) {
                $sideBlock = $pos->getSide($side);
                if (!$this->world->isInWorld((int)$sideBlock->x, (int)$sideBlock->y, (int)$sideBlock->z)) {
                    continue;
                }
                if (!isset($this->affectedBlocks[$index = World::blockHash((int)$sideBlock->x, (int)$sideBlock->y, (int)$sideBlock->z)]) and !isset($updateBlocks[$index])) {
                    $ev = new BlockUpdateEvent($this->world->getBlockAt((int)$sideBlock->x, (int)$sideBlock->y, (int)$sideBlock->z));
                    $ev->call();
                    if (!$ev->isCancelled()) {
                        foreach ($this->world->getNearbyEntities(AxisAlignedBB::one()->offset($sideBlock->x, $sideBlock->y, $sideBlock->z)->expand(1, 1, 1)) as $entity) {
                            $entity->onNearbyBlockChange();
                        }
                        $ev->getBlock()->onNearbyBlockChange();
                    }
                    $updateBlocks[$index] = true;
                }
            }
            $send[] = $pos->subtractVector($source);
        }
        unset(RecursiveEnchant::$isUsing[$this->what->getName()]);

        $this->world->addParticle($source, new HugeExplodeSeedParticle());
        $this->world->addSound($source, new ExplodeSound());
        return true;
    }
}