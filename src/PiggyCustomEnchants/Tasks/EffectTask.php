<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\entity\Effect;
use pocketmine\scheduler\PluginTask;

/**
 * Class EffectTask
 * @package PiggyCustomEnchants\Tasks
 */
class EffectTask extends PluginTask
{
    private $plugin;

    /**
     * EffectTask constructor.
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
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::OXYGENATE);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::WATER_BREATHING);
                $effect->setAmplifier(0);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getHelmet(), CustomEnchants::GLOWING);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::NIGHT_VISION);
                $effect->setAmplifier(0);
                $effect->setDuration(220);
                $effect->setVisible(false);
                $player->addEffect($effect);
                $this->plugin->glowing[$player->getLowerCaseName()] = true;
            } else {
                if (isset($this->plugin->glowing[$player->getLowerCaseName()])) {
                    $player->removeEffect(Effect::NIGHT_VISION);
                    unset($this->plugin->glowing[$player->getLowerCaseName()]);
                }
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getChestplate(), CustomEnchants::ENRAGED);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::STRENGTH);
                $effect->setAmplifier($enchantment->getLevel() - 1);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getBoots(), CustomEnchants::GEARS);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::SPEED);
                $effect->setAmplifier(0);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            foreach ($player->getInventory()->getArmorContents() as $slot => $armor) {
                $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::OBSIDIANSHIELD);
                if ($enchantment !== null) {
                    $effect = Effect::getEffect(Effect::FIRE_RESISTANCE);
                    $effect->setAmplifier(0);
                    $effect->setDuration(10);
                    $effect->setVisible(false);
                    $player->addEffect($effect);
                }
                $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::OVERLOAD);
                if ($enchantment !== null) {
                    if (!isset($this->plugin->overload[$player->getLowerCaseName() . "||" . $slot])) {
                        $player->setMaxHealth($player->getMaxHealth() + (2 * $enchantment->getLevel()));
                        $player->setHealth($player->getHealth() + (2 * $enchantment->getLevel()) < $player->getMaxHealth() ? $player->getHealth() + (2 * $enchantment->getLevel()) : $player->getMaxHealth());
                        $this->plugin->overload[$player->getLowerCaseName() . "||" . $slot] = $enchantment->getLevel();
                    }
                } else {
                    if (isset($this->plugin->overload[$player->getLowerCaseName() . "||" . $slot])) {
                        $level = $this->plugin->overload[$player->getLowerCaseName() . "||" . $slot];
                        $player->setMaxHealth($player->getMaxHealth() - (2 * $level));
                        if ($player->isAlive()) {
                            $player->setHealth($player->getHealth() - (2 * $level) < $player->getMaxHealth() ? ($player->getHealth() - (2 * $level) <= 0 ? 1 : $player->getHealth() - (2 * $level)) : $player->getMaxHealth());
                        }
                        unset($this->plugin->overload[$player->getLowerCaseName() . "||" . $slot]);
                    }
                }
            }
        }
    }
}