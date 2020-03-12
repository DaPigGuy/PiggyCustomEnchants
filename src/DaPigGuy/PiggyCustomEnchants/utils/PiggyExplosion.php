<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RecursiveEnchant;
use pocketmine\block\TNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\level\Explosion;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;
use pocketmine\tile\Container;
use pocketmine\tile\Tile;

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

        $ev = new EntityExplodeEvent($this->what, $this->source, $this->affectedBlocks, (1 / $this->size) * 100);
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
        $list = $this->level->getNearbyEntities($explosionBB, $this->what);
        foreach ($list as $entity) {
            $distance = $entity->distance($this->source) / $explosionSize;
            if ($distance <= 1) {
                $motion = $entity->subtract($this->source)->normalize();
                $impact = (1 - $distance) * ($exposure = 1);
                $damage = (int)((($impact * $impact + $impact) / 2) * 8 * $explosionSize + 1);

                $ev = new EntityDamageByEntityEvent($this->what, $entity, EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, $damage);
                $entity->attack($ev);
                $entity->setMotion($motion->multiply($impact));
            }
        }

        $item = $this->what->getInventory()->getItemInHand();
        RecursiveEnchant::$isUsing[$this->what->getName()] = true;
        foreach ($this->affectedBlocks as $key => $block) {
            $drops = $this->what->isCreative() || $block->equals($source) ? [] : $block->getDrops($item);
            $t = $this->level->getTileAt($block->getFloorX(), $block->getFloorY(), $block->getFloorZ());
            if ($t instanceof Container) {
                $drops = array_merge($drops, $t->getInventory()->getContents());
            }

            $ev = new BlockBreakEvent($this->what, $block, $item, true, $drops);
            $ev->call();
            if ($ev->isCancelled()) {
                unset($this->affectedBlocks[$key]);
                continue;
            }

            if ($t instanceof Tile) {
                if ($t instanceof Chest) {
                    $t->unpair();
                }
                $t->close();
            }

            if ($block instanceof TNT) {
                $block->ignite(mt_rand(10, 30));
            } else {
                foreach ($ev->getDrops() as $drop) {
                    $this->level->dropItem($block->add(0.5, 0.5, 0.5), $drop);
                }
            }

            $this->level->setBlockIdAt($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), 0);
            $this->level->setBlockDataAt($block->getFloorX(), $block->getFloorY(), $block->getFloorZ(), 0);

            $pos = new Vector3($block->x, $block->y, $block->z);
            for ($side = 0; $side <= 5; $side++) {
                $sideBlock = $pos->getSide($side);
                if (!$this->level->isInWorld($sideBlock->getFloorX(), $sideBlock->getFloorY(), $sideBlock->getFloorZ())) {
                    continue;
                }
                if (!isset($this->affectedBlocks[$index = ((($sideBlock->x) & 0xFFFFFFF) << 36) | ((($sideBlock->y) & 0xff) << 28) | (($sideBlock->z) & 0xFFFFFFF)]) and !isset($updateBlocks[$index])) {
                    $ev = new BlockUpdateEvent($this->level->getBlockAt($sideBlock->getFloorX(), $sideBlock->getFloorY(), $sideBlock->getFloorZ()));
                    $ev->call();
                    if (!$ev->isCancelled()) {
                        foreach ($this->level->getNearbyEntities(new AxisAlignedBB($sideBlock->x - 1, $sideBlock->y - 1, $sideBlock->z - 1, $sideBlock->x + 2, $sideBlock->y + 2, $sideBlock->z + 2)) as $entity) {
                            $entity->onNearbyBlockChange();
                        }
                        $ev->getBlock()->onNearbyBlockChange();
                    }
                    $updateBlocks[$index] = true;
                }
            }
            $send[] = new Vector3($block->x - $source->x, $block->y - $source->y, $block->z - $source->z);
        }
        unset(RecursiveEnchant::$isUsing[$this->what->getName()]);
        $this->level->addParticle(new HugeExplodeSeedParticle($source));
        $this->level->broadcastLevelSoundEvent($source, LevelSoundEventPacket::SOUND_EXPLODE);
        return true;
    }
}