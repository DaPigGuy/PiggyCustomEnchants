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
    /** @var Player */
    protected $player;
    /** @var Main */
    private $plugin;

    /**
     * PiggyExplosion constructor.
     * @param Position $center
     * @param $size
     * @param Player $player
     * @param Main $plugin
     */
    public function __construct(Position $center, $size, Player $player = null, Main $plugin)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        parent::__construct($center, $size, $player);
    }

    /**
     * @return bool
     */
    public function explodeB(): bool
    {
        $result = parent::explodeB();
        foreach ($this->affectedBlocks as $index => $block) {
            if ($block->equals($this->source)) {
                continue;
            }
            $item = $this->player->getInventory()->getItemInHand();
            $ev = new BlockBreakEvent($this->player, $block, $item, true, $this->player->isCreative() ? [] : $block->getDrops($item), $this->player->isCreative() ? 0 : $block->getXpDropForTool($item));
            $this->plugin->getServer()->getPluginManager()->callEvent($ev);
            if ($ev->isCancelled()) {
                unset($this->affectedBlocks[$index]);
            } else {
                foreach ($ev->getDrops() as $drop) {
                    $this->level->dropItem($block->add(0.5, 0.5, 0.5), $drop);
                }
            }
        }
        return $result;
    }
}