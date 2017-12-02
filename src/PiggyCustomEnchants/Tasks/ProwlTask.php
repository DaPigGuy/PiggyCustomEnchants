<?php

namespace PiggyCustomEnchants\Tasks;


use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\scheduler\PluginTask;

/**
 * Class ProwlTask
 * @package PiggyCustomEnchants\Tasks
 */
class ProwlTask extends PluginTask
{
    private $plugin;

    /**
     * ProwlTask constructor.
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
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getChestplate(), CustomEnchants::PROWL);
            if ($enchantment !== null && $player->isSneaking()) {
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                    $p->hidePlayer($player);
                }
                $effect = Effect::getEffect(Effect::SLOWNESS);
                $effect->setAmplifier(0);
                $effect->setDuration(5);
                $effect->setVisible(false);
                $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, true);
                $player->addEffect($effect);
                $this->plugin->prowl[$player->getLowerCaseName()] = true;
            } else {
                if (isset($this->plugin->prowl[$player->getLowerCaseName()])) {
                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                        $p->showPlayer($player);
                        $p->removeEffect(Effect::SLOWNESS);
                        if (!$player->hasEffect(Effect::INVISIBILITY)) {
                            $player->setGenericFlag(Entity::DATA_FLAG_INVISIBLE, false);
                        }
                    }
                }
            }
        }
    }
}