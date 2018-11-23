<?php

namespace DaPigGuy\PiggyCustomEnchants\Entities;

use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByEntityEvent;

/**
 * Class PiggyFireball
 * @package DaPigGuy\PiggyCustomEnchants\Entities
 */
class PiggyFireball extends PiggyProjectile
{
    /**
     * Used to replace const NETWORK_ID to resolve registration conflicts with vanilla entities
     * @var int
     */
    const TYPE_ID = 94;
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

    /**
     * @param Entity $entity
     */
    public function onCollideWithEntity(Entity $entity)
    {
        $ev = new EntityCombustByEntityEvent($this, $entity, 5);
        $ev->call();
        if (!$ev->isCancelled()) {
            $entity->setOnFire($ev->getDuration());
        }
        parent::onCollideWithEntity($entity);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        if (!$this->isFlaggedForDespawn()) {
            if ($this->blockHit !== null) {
                if (($this->isCollidedHorizontally || $this->isCollidedVertically) && $this->getLevel()->getBlock($this)->canBeFlowedInto() && Main::$blazeFlames) {
                    $this->getLevel()->setBlock($this, Block::get(Block::FIRE));
                }
                $this->flagForDespawn();
            }
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        return $hasUpdate;
    }
}