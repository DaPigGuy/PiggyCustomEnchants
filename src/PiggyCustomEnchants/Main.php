<?php

namespace PiggyCustomEnchants;

use PiggyCustomEnchants\Commands\CustomEnchantCommand;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Entities\Fireball;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Main
 * @package PiggyCustomEnchants
 */
class Main extends PluginBase
{
    public $vampirecd;
    public $cloakingcd;
    public $berserkercd;
    public $endershiftcd;

    public $breakingTree;
    public $mined;

    public $nofall;

    public function onEnable()
    {
        CustomEnchants::init();
        Entity::registerEntity(Fireball::class);
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
     * @param $enchant
     * @param $level
     * @param Player $player
     * @param CommandSender $sender
     * @param null $slot
     * @return bool
     * @internal param $ench
     */
    public function addEnchantment(Item $item, $enchant, $level, Player $player, CommandSender $sender = null, $slot = null)
    {
        //TODO: Check if item can get enchant
        $enchant = CustomEnchants::getEnchantByName($enchant);
        if ($enchant == null) {
            if ($sender !== null) {
                $sender->sendMessage("§cInvalid enchantment.");
            }
            return false;
        }
        $enchant->setLevel($level);
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
            if ($entry["id"] === $enchant->getId()) {
                $tag->ench->{$k} = new CompoundTag("", [
                    "id" => new ShortTag("id", $enchant->getId()),
                    "lvl" => new ShortTag("lvl", $enchant->getLevel())
                ]);
                $item->setNamedTag($tag);
                $item->setCustomName(str_replace(TextFormat::GRAY . $enchant->getName() . " " . $this->getRomanNumber($entry["lvl"]), TextFormat::GRAY . $enchant->getName() . " " . $this->getRomanNumber($enchant->getLevel()), $item->getName()));
                $found = true;
                break;
            }
        }
        if (!$found) {
            $tag->ench->{count($tag->ench) + 1} = new CompoundTag($enchant->getName(), [
                "id" => new ShortTag("id", $enchant->getId()),
                "lvl" => new ShortTag("lvl", $enchant->getLevel())
            ]);
            $level = $this->getRomanNumber($enchant->getLevel());
            $item->setNamedTag($tag);
            $item->setCustomName($item->getName() . "\n" . TextFormat::GRAY . $enchant->getName() . " " . $level);
        }
        if ($slot == null) {
            $player->getInventory()->setItemInHand($item);
        } else {
            $player->getInventory()->setItem($slot, $item);
        }
        if ($sender !== null) {
            $sender->sendMessage("§aEnchanting suceeded.");
        }
        return true;
    }

    /**
     * @param Item $item
     * @param CustomEnchants $enchant
     * @param Player $player
     * @param $slot
     * @return bool|Item
     * @internal param CustomEnchants $ench
     */
    public function removeEnchantment(Item $item, CustomEnchants $enchant, Player $player, $slot)
    {
        if (!$item->hasEnchantments()) {
            return false;
        }
        $tag = $item->getNamedTag();
        $enchants = [];
        foreach ($tag->ench as $k => $enchantment) {
            if ($enchantment["id"] !== $enchant->getId()) {
                array_push($enchants, CustomEnchants::getEnchantment($enchantment["id"])->setLevel($enchantment["lvl"]));
            }
        }
        $item = Item::get($item->getId(), $item->getDamage(), $item->getCount());
        foreach ($enchants as $ench) {
            $this->addEnchantment($item, str_replace(" ", "", $ench->getName()), $ench->getLevel(), $player, null, $slot);
        }
        return $item;
    }

    /**
     * @param $integer
     * @return string
     */
    public function getRomanNumber($integer) //Thank you @Muqsit!
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $return;
    }

    /**
     * @param Item $item
     * @param CustomEnchants $enchant
     * @param $event
     * @return bool
     */
    public function canUse(Item $item, CustomEnchants $enchant, $event = null)
    {
        //TODO: Implement
        return false;
    }
}