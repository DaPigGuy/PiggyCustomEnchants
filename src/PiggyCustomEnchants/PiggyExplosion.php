<?php

namespace PiggyCustomEnchants;


use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\Player;

/**
 * Class PiggyExplosion
 * @package PiggyCustomEnchants
 */
class PiggyExplosion extends Explosion
{
    protected $what;
    private $plugin;

    /**
     * PiggyExplosion constructor.
     * @param Position $center
     * @param $size
     * @param null $what
     * @param Main $plugin
     */
    public function __construct(Position $center, $size, $what = null, Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($center, $size, $what);
    }

    /**
     * @return bool
     */
    public function explodeB(): bool
    {
        foreach ($this->affectedBlocks as $index => $block) {
            if ($this->what instanceof Player) {
                $ev = new BlockBreakEvent($this->what, $block, $this->what->getInventory()->getItemInHand());
                $this->plugin->getServer()->getPluginManager()->callEvent($ev);
                if($ev->isCancelled()){
                    unset($this->affectedBlocks[$index]);
                }
            }
        }
        return parent::explodeB();
    }
}