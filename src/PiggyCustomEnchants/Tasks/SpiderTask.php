<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\scheduler\PluginTask;

/**
 * Class SpiderTask
 * @package PiggyCustomEnchants\Tasks
 */
class SpiderTask extends PluginTask
{
    private $plugin;

    /**
     * SpiderTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $player->getInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::SPIDER);
            if($enchantment !== null){
                $blocks = $player->getLevel()->getBlock($player)->getHorizontalSides(); //getBlocksAround() returns an empty array...
                $nonair = 0;
                foreach ($blocks as $block){
                    if($block->getId() !== Block::AIR && $block->isSolid()){
                        $nonair++;
                    }
                }
                if($nonair > 0){
                    if(!$player->getGenericFlag(Entity::DATA_FLAG_WALLCLIMBING)){
                        $player->setGenericFlag(Entity::DATA_FLAG_WALLCLIMBING, true);
                    }
                    $player->resetFallDistance();
                }else{
                    if($player->getGenericFlag(Entity::DATA_FLAG_WALLCLIMBING)){
                        $player->setGenericFlag(Entity::DATA_FLAG_WALLCLIMBING, false);
                    }
                }
            }else{
                if($player->getGenericFlag(Entity::DATA_FLAG_WALLCLIMBING)){
                    $player->setGenericFlag(Entity::DATA_FLAG_WALLCLIMBING, false);
                }
            }
        }
    }
}