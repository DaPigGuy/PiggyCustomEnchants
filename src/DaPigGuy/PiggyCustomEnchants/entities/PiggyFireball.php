<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\entities;

use DaPigGuy\PiggyCustomEnchants\utils\AllyChecks;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\Player;

class PiggyFireball extends PiggyProjectile
{
    const NETWORK_ID = Entity::SMALL_FIREBALL;

    /** @var float */
    public $width = 0.5;
    /** @var float */
    public $length = 0.5;
    /** @var float */
    public $height = 0.5;

    /** @var float */
    protected $drag = 0.01;
    /** @var float */
    protected $gravity = 0.05;

    /** @var int */
    protected $damage = 5;

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        $owner = $this->getOwningEntity();
        if (!$owner instanceof Player || !AllyChecks::isAlly($owner, $entityHit)) {
            $ev = new EntityCombustByEntityEvent($this, $entityHit, 5);
            $ev->call();
            if (!$ev->isCancelled()) {
                $entityHit->setOnFire($ev->getDuration());
            }
        }
        parent::onHitEntity($entityHit, $hitResult);
    }

    public function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
        parent::onHitBlock($blockHit, $hitResult);
    }
}