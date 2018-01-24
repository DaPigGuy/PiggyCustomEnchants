<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
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
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::HASTE);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::HASTE);
                $effect->setAmplifier($enchantment->getLevel() - 1);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::OXYGENATE);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::WATER_BREATHING);
                $effect->setAmplifier(0);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::GLOWING);
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
            $enchantment = $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::ENRAGED);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::STRENGTH);
                $effect->setAmplifier($enchantment->getLevel() - 1);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::GEARS);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::SPEED);
                $effect->setAmplifier(0);
                $effect->setDuration(10);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $shielded = 0;
            foreach ($player->getArmorInventory()->getContents() as $slot => $armor) {
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::OBSIDIANSHIELD);
                if ($enchantment !== null) {
                    $effect = Effect::getEffect(Effect::FIRE_RESISTANCE);
                    $effect->setAmplifier(0);
                    $effect->setDuration(10);
                    $effect->setVisible(false);
                    $player->addEffect($effect);
                }
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::OVERLOAD);
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
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::SHIELDED);
                if ($enchantment !== null) {
                    $shielded += $enchantment->getLevel();
                    $effect = Effect::getEffect(Effect::RESISTANCE);
                    $effect->setAmplifier($shielded - 1);
                    $effect->setDuration(10);
                    $effect->setVisible(false);
                    $player->addEffect($effect);
                }
            }
        }
    }
}