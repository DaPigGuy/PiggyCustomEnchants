<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\entity\Effect;
use pocketmine\entity\Living;
use pocketmine\level\particle\DustParticle;
use pocketmine\scheduler\PluginTask;

/**
 * Class PoisonousGasTask
 * @package PiggyCustomEnchants\Tasks
 */
class PoisonousGasTask extends PluginTask
{
    private $plugin;

    /**
     * PoisonousGasTask constructor.
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
            foreach ($player->getArmorInventory()->getContents() as $armor) {
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::POISONOUSCLOUD);
                if ($enchantment !== null) {
                    $radius = $enchantment->getLevel() * 3;
                    foreach ($player->getLevel()->getEntities() as $entity) {
                        if ($entity !== $player && $entity instanceof Living && $entity->distance($player) <= $radius) {
                            $effect = Effect::getEffect(Effect::POISON);
                            $effect->setAmplifier($enchantment->getLevel() - 1);
                            $effect->setDuration($enchantment->getLevel() * 100);
                            $effect->setVisible(false);
                            $entity->addEffect($effect);
                        }
                    }
                    if (!isset($this->plugin->gasParticleTick[$player->getLowerCaseName()])) {
                        $this->plugin->gasParticleTick[$player->getLowerCaseName()] = 0;
                    }
                    $this->plugin->gasParticleTick[$player->getLowerCaseName()]++;
                    if ($this->plugin->gasParticleTick[$player->getLowerCaseName()] >= 20) {
                        for ($x = -$radius; $x <= $radius; $x += 0.25) {
                            for ($y = -$radius; $y <= $radius; $y += 0.25) {
                                for ($z = -$radius; $z <= $radius; $z += 0.25) {
                                    $random = mt_rand(1, 800 * $enchantment->getLevel());
                                    if ($random == 800 * $enchantment->getLevel()) {
                                        $player->getLevel()->addParticle(new DustParticle($player->add($x, $y, $z), 34, 139, 34));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}