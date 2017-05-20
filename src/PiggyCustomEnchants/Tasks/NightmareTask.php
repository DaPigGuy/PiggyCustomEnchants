<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

/**
 * Class NightmareTask
 * @package PiggyCustomEnchants
 */
class NightmareTask extends PluginTask
{
    private $plugin;
    private $player;
    private $position;
    private $time = 0;
    private $id = null;

    /**
     * NightmareTask constructor.
     * @param Main $plugin
     * @param Player $player
     * @param Position $position
     */
    public function __construct(Main $plugin, Player $player, Position $position)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
        $this->position = $position;
    }

    /**
     * @param $currentTick
     */
    public function onRun($currentTick)
    {
        $position = $this->position;
        $this->time++;
        $rand = mt_rand(0, 1000);
        /*if ($rand <= 10 && $this->id == null) {
            $id = Entity::$entityCount++;
            $this->id = [$id, $this->time + 20];

            //Borrow from Slapper-Rotation (I suck at math)
            $xdiff = $this->player->x - $position->x;
            $zdiff = $this->player->z - $position->z;
            $angle = atan2($zdiff, $xdiff);
            $yaw = (($angle * 180) / M_PI) - 90;
            $ydiff = $this->player->y - $position->y;
            $v = new Vector2($position->x, $position->z);
            $dist = $v->distance($this->player->x, $this->player->z);
            $angle = atan2($dist, $ydiff);
            $pitch = (($angle * 180) / M_PI) - 90;

            $pk = new AddEntityPacket();
            $pk->eid = $id;
            $pk->type = 48;
            $pk->x = $position->x;
            $pk->y = $position->y;
            $pk->z = $position->z;
            $pk->speedX = 0;
            $pk->speedY = 0;
            $pk->speedZ = 0;
            $pk->yaw = $yaw;
            $pk->pitch = $pitch;
            $this->player->dataPacket($pk);
        }
        if ($this->id !== null) {
            $id = $this->id[0];
            $time = $this->id[1];
            if ($time <= $this->time) {
                $pk = new RemoveEntityPacket();
                $pk->eid = $id;
                $this->player->dataPacket($pk);
                $this->id = null;
            }
        }*/
        for ($x = $position->x - 1; $x <= $position->x + 1; $x++) {
            for ($y = $position->y - 1; $y <= $position->y + 2; $y++) {
                for ($z = $position->z - 1; $z <= $position->z + 1; $z++) {
                    $pos = new Position($x, $y, $z, $position->getLevel());
                    if ($pos->equals($position->add(0, 1)) !== true) {
                        if ($this->time >= 20 * 60) {
                            $position->getLevel()->sendBlocks([$this->player], [$position->getLevel()->getBlock($pos)]);
                        } else {
                            if ($pos->equals($position) !== true) {
                                $block = Block::get(Block::BEDROCK);
                            } else {
                                $block = Block::get(Block::LAVA);
                            }
                            $block->setComponents($pos->x, $pos->y, $pos->z);
                            $position->getLevel()->sendBlocks([$this->player], [$block]);
                        }
                    }
                }
            }
        }
        if ($this->time >= 20 * 60) {
            if ($this->id !== null) {
                $id = $this->id[0];
                $pk = new RemoveEntityPacket();
                $pk->eid = $id;
                $this->player->dataPacket($pk);
                $this->id = null;

            }
            unset($this->plugin->nightmare[strtolower($this->player->getName())]);
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getHandler()->getTaskId());
        }
    }
}