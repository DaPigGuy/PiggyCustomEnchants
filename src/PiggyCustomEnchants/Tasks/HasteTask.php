<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\entity\Effect;
use pocketmine\scheduler\PluginTask;

/**
 * Class HasteTask
 * @package PiggyCustomEnchants\Tasks
 */
class HasteTask extends PluginTask
{
    private $plugin;

    /**
     * HasteTask constructor.
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
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::HASTE);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::HASTE);
                $effect->setAmplifier($enchantment->getLevel() - 1);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
        }
    }
}