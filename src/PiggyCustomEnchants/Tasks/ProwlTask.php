<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;

/**
 * Class ProwlTask
 * @package PiggyCustomEnchants\Tasks
 */
class ProwlTask extends Task
{
    /** @var Main */
    private $plugin;

    /**
     * ProwlTask constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $enchantment = $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::PROWL);
            if ($enchantment !== null && $player->isSneaking()) {
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
                    $p->hidePlayer($player);
                }
                $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 5, 0, false);
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