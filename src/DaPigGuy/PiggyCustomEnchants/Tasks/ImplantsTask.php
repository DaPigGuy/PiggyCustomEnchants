<?php

namespace DaPigGuy\PiggyCustomEnchants\Tasks;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use DaPigGuy\PiggyCustomEnchants\Main;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class ImplantsTask
 * @package DaPigGuy\PiggyCustomEnchants\Tasks
 */
class ImplantsTask extends Task
{
    /** @var Main */
    private $plugin;
    /** @var Player */
    private $player;

    /**
     * ImplantsTask constructor.
     * @param Main   $plugin
     * @param Player $player
     */
    public function __construct(Main $plugin, Player $player)
    {
        $this->plugin = $plugin;
        $this->player = $player;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        $player = $this->player;
        if ($player->isOnline() && $player->isAlive() && ($enchantment = $player->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::IMPLANTS)) !== null) {
            if (!$this->plugin->checkBlocks($player, [Block::WATER, Block::STILL_WATER, Block::FLOWING_WATER], -1)) {
                $this->cancel();
                return;
            }
            if ($player->getAirSupplyTicks() < $player->getMaxAirSupplyTicks()) {
                $player->setAirSupplyTicks($player->getAirSupplyTicks() + ($enchantment->getLevel() * 40) > $player->getMaxAirSupplyTicks() ? $player->getMaxAirSupplyTicks() : $player->getAirSupplyTicks() + ($enchantment->getLevel() * 40));
            } else {
                $this->cancel();
            }
        } else {
            $this->cancel();
        }
    }

    public function cancel()
    {
        unset($this->plugin->implants[$this->player->getLowerCaseName()]);
        $this->plugin->getScheduler()->cancelTask($this->getHandler()->getTaskId());
    }
}