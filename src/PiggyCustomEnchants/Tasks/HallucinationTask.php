<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\Main;
use pocketmine\block\Block;

use pocketmine\level\Position;


use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;

use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

/**
 * Class HallucinationTask
 * @package PiggyCustomEnchants
 */
class HallucinationTask extends PluginTask
{
    private $plugin;
    private $player;
    private $position;
    private $time = 0;

    /**
     * HallucinationTask constructor.
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
        for ($x = $position->x - 1; $x <= $position->x + 1; $x++) {
            for ($y = $position->y - 1; $y <= $position->y + 2; $y++) {
                for ($z = $position->z - 1; $z <= $position->z + 1; $z++) {
                    $pos = new Position($x, $y, $z, $position->getLevel());
                    if ($this->time >= 20 * 60) {
                        $position->getLevel()->sendBlocks([$this->player], [$position->getLevel()->getBlock($pos)]);
                    } else {
                        if ($pos->equals($position) !== true) {
                            if ($pos->equals($position->add(0, 1)) !== true) {
                                $block = Block::get(Block::BEDROCK);
                            } else {
                                $block = Block::get(Block::WALL_SIGN);
                                $nbt = new NBT(NBT::LITTLE_ENDIAN);
                                $nbt->setData(new CompoundTag("", [
                                    new StringTag("id", Tile::SIGN),
                                    new StringTag("Text1", TextFormat::RED . "You seem to be"),
                                    new StringTag("Text2", TextFormat::RED . "hallucinating..."),
                                    new StringTag("Text3", ""),
                                    new StringTag("Text4", ""),
                                    new IntTag("x", $pos->x),
                                    new IntTag("y", $pos->y),
                                    new IntTag("z", $pos->z)
                                ]));
                                $pk = new BlockEntityDataPacket();
                                $pk->x = $pos->x;
                                $pk->y = $pos->y;
                                $pk->z = $pos->z;
                                $pk->namedtag = $nbt->write(true);
                                $this->player->dataPacket($pk);
                            }
                        } else {
                            $block = Block::get(Block::LAVA);
                        }
                        $block->setComponents($pos->x, $pos->y, $pos->z);
                        $position->getLevel()->sendBlocks([$this->player], [$block]);
                    }
                }
            }
        }
        if ($this->time >= 20 * 60) {
            unset($this->plugin->hallucination[strtolower($this->player->getName())]);
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getHandler()->getTaskId());
        }
    }
}