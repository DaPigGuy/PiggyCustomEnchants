<?php

namespace PiggyCustomEnchants;

use PiggyCustomEnchants\Commands\CustomEnchantCommand;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

/**
 * Class Main
 * @package PiggyCustomEnchants
 */
class Main extends PluginBase
{
    public function onEnable()
    {
        CustomEnchants::init();
        $this->getServer()->getCommandMap()->register("customenchant", new CustomEnchantCommand("customenchant", $this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("§aEnabled");
    }

    /**
     * @param Item $item
     * @param $id
     * @return null|CustomEnchants
     */
    public function getEnchantment(Item $item, $id)
    {
        if (!$item->hasEnchantments()) {
            return null;
        }
        foreach ($item->getNamedTag()->ench as $entry) {
            if ($entry["id"] === $id) {
                $e = CustomEnchants::getEnchantment($entry["id"]);
                $e->setLevel($entry["lvl"]);
                return $e;
            }
        }
        return null;
    }

    /**
     * @param Item $item
     * @param $ench
     * @param $level
     * @param Player $player
     * @return bool
     */
    public function addEnchantment(Item $item, $ench, $level, Player $player)
    {
        $ench = CustomEnchants::getEnchantByName($ench);
        if ($ench == null) {
            $player->sendMessage("Invalid enchantment.");
            return false;
        }
        $ench->setLevel($level);
        if (!$item->hasCompoundTag()) {
            $tag = new CompoundTag("", []);
        } else {
            $tag = $item->getNamedTag();
        }
        if (!isset($tag->ench)) {
            $tag->ench = new ListTag("ench", []);
            $tag->ench->setTagType(NBT::TAG_Compound);
        }
        $found = false;
        foreach ($tag->ench as $k => $entry) {
            if ($entry["id"] === $ench->getId()) {
                $tag->ench->{$k} = new CompoundTag("", [
                    "id" => new ShortTag("id", $ench->getId()),
                    "lvl" => new ShortTag("lvl", $ench->getLevel())
                ]);
                $found = true;
                break;
            }
        }
        if (!$found) {
            $tag->ench->{count($tag->ench) + 1} = new CompoundTag($ench->getName(), [
                "id" => new ShortTag("id", $ench->getId()),
                "lvl" => new ShortTag("lvl", $ench->getLevel())
            ]);
            $item->setNamedTag($tag);
        }
        $level = $ench->getLevel();
        switch ($level) { //If 1-5 use roman numerals
            case 1:
                $level = "I";
                break;
            case 2:
                $level = "II";
                break;
            case 3:
                $level = "III";
                break;
            case 4:
                $level = "IV";
                break;
            case 5:
                $level = "V";
                break;
        }
        $item->setCustomName($item->getName() . "\n" . $ench->getName() . " " . $level);
        $player->getInventory()->setItemInHand($item);
    }
}