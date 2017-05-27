<?php

namespace PiggyCustomEnchants;

use PiggyCustomEnchants\Commands\CustomEnchantCommand;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Entities\Fireball;
use PiggyCustomEnchants\Entities\PigProjectile;
use PiggyCustomEnchants\Tasks\SizeTask;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;

use pocketmine\item\Armor;
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
    const MAX_LEVEL = 0;
    const NOT_COMPATIBLE = 1;
    const NOT_COMPATIBLE_WITH_OTHER_ENCHANT = 2;

    public $vampirecd;
    public $cloakingcd;
    public $berserkercd;
    public $endershiftcd;
    public $bountyhuntercd;
    public $shrinkcd;
    public $growcd;

    public $breakingTree;
    public $mined;

    public $nofall;

    public $hallucination;

    public $shrunk;
    public $grew;
    public $wasshrunk; //Temporary

    public $enchants = [
        //id => ["name", "slot", "trigger", "rarity", maxlevel"]
        CustomEnchants::AERIAL => ["Aerial", "Weapons", "Damage", "Common", 5],
        CustomEnchants::AUTOREPAIR => ["Autorepair", "Damageable", "Move", "Uncommon", 6],
        CustomEnchants::BERSERKER => ["Berserker", "Armor", "Damaged", "Rare", 5],
        CustomEnchants::CLOAKING => ["Cloaking", "Armor", "Damaged", "Uncommon", 5],
        CustomEnchants::BLAZE => ["Blaze", "Bow", "Shoot", "Rare", 1],
        CustomEnchants::BLIND => ["Blind", "Weapons", "Damage", "Common", 5],
        CustomEnchants::BOUNTYHUNTER => ["Bounty Hunter", "Bow", "Damage", "", 5],
        CustomEnchants::CHARGE => ["Charge", "Weapons", "Damage", "Uncommon", 5],
        CustomEnchants::CRIPPLINGSTRIKE => ["Cripple", "Weapons", "Damage", "Common", 5],
        CustomEnchants::CRIPPLE => ["Cripple", "Weapons", "Damage", "Common", 5],
        CustomEnchants::CURSED => ["Cursed", "Armor", "Damaged", "Uncommon", 5],
        CustomEnchants::DEATHBRINGER => ["Deathbringer", "Weapons", "Damage", "Rare", 5],
        CustomEnchants::DISARMING => ["Disarming", "Weapons", "Damage", "Uncommon", 1],
        CustomEnchants::DRUNK => ["Drunk", "Armor", "Damaged", "Rare", 5],
        CustomEnchants::ENDERSHIFT => ["Endershift", "Armor", "Damaged", "Rare", 5],
        CustomEnchants::ENERGIZING => ["Energizing", "Tools", "Break", "Uncommon", 5],
        CustomEnchants::ENLIGHTED => ["Enlighted", "Armor", "Damaged", "Uncommon", 5],
        CustomEnchants::EXPLOSIVE => ["Explosive", "Tools", "Break", "Rare", 5],
        CustomEnchants::FROZEN => ["Frozen", "Armor", "Damaged", "Rare", 5],
        CustomEnchants::GEARS => ["Gears", "Boots", "Equip", "Uncommon", 5],
        CustomEnchants::GLOWING => ["Glowing", "Helmets", "Equip", "Common", 1],
        CustomEnchants::GOOEY => ["Gooey", "Weapons", "Damage", "Uncommon", 5],
        CustomEnchants::GRAPPLING => ["Grappling", "Bow", "Projectile_Hit", "Rare", 1],
        CustomEnchants::GROW => ["Grow", "Armor", "Sneak", "", 5], //TODO: Pick rarity
        CustomEnchants::HALLUCINATION => ["Hallucination", "Weapons", "Damage", "Mythic", 5],
        CustomEnchants::HARDENED => ["Hardened", "Armor", "Damaged", "", 5], //TODO: Pick rarity
        CustomEnchants::HEADHUNTER => ["Headhunter", "Bow", "Damage", "Uncommon", 5],
        CustomEnchants::HEALING => ["Healing", "Bow", "Damage", "Rare", 5],
        CustomEnchants::LIFESTEAL => ["Lifesteal", "Weapons", "Damage", "Common", 5],
        CustomEnchants::LUMBERJACK => ["Lumberjack", "Axe", "Break", "Rare", 1],
        CustomEnchants::MOLOTOV => ["Molotov", "Bow", "Projectile_Hit", "Uncommon", 5],
        CustomEnchants::MOLTEN => ["Molten", "Armor", "Damaged", "Rare", 5],
        CustomEnchants::OBSIDIANSHIELD => ["Obsidian Shield", "Armor", "Equip", "Common", 5],
        CustomEnchants::PARALYZE => ["Paralyze", "Bow", "Damage", "Rare", 5],
        CustomEnchants::PIERCING => ["Piercing", "Bow", "Damage", "", 5], //TODO: Pick rarity
        CustomEnchants::POISON => ["Poison", "Weapons", "Damage", "Uncommon", 5],
        CustomEnchants::POISONED => ["Poisoned", "Armor", "Damaged", "Uncommon", 5],
        CustomEnchants::PORKIFIED => ["Porkified", "Bow", "Shoot", "Mythic", 3],
        CustomEnchants::QUICKENING => ["Quickening", "Tools", "Break", "Uncommon", 5],
        CustomEnchants::REVIVE => ["Revive", "Armor", "Death", "Rare", 5],
        CustomEnchants::REVULSION => ["Revulsion", "Armor", "Damaged", "Uncommon", 5],
        CustomEnchants::SELFDESTRUCT => ["Self Destruct", "Armor", "Damaged", "Rare", 5],
        CustomEnchants::SHRINK => ["Shrink", "Armor", "Sneak", "", 2], //TODO: Pick rarity
        CustomEnchants::SHUFFLE => ["Shuffle", "Bow", "Damage", "Rare", 1],
        CustomEnchants::SMELTING => ["Smelting", "Tools", "Break", "Uncommon", 1],
        CustomEnchants::SOULBOUND => ["Soulbound", "Global", "Death", "Mythic", 1],
        CustomEnchants::SPRINGS => ["Springs", "Boots", "Equip", "Uncommon", 5],
        CustomEnchants::STOMP => ["Stomp", "Boots", "Fall_Damage", "Uncommon", 5],
        CustomEnchants::TELEPATHY => ["Telepathy", "Tools", "Break", "Rare", 1],
        CustomEnchants::VAMPIRE => ["Vampire", "Weapons", "Damage", "Uncommon", 1],
        CustomEnchants::VOLLEY => ["Volley", "Bow", "Shoot", "Uncommon", 5],
        CustomEnchants::WITHER => ["Wither", "Weapons", "Damage", "Uncommon", 5]
    ];

    public function onEnable()
    {
        if (!$this->isSpoon()) {
            $this->initCustomEnchants();
            Entity::registerEntity(Fireball::class);
            Entity::registerEntity(PigProjectile::class);
            $this->getServer()->getCommandMap()->register("customenchant", new CustomEnchantCommand("customenchant", $this));
           $this->getServer()->getScheduler()->scheduleRepeatingTask(new SizeTask($this), 20);
            $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
            $this->getLogger()->info("§aEnabled.");
        }
    }

    /**
     * @return bool
     */
    public function isSpoon()
    {
        if ($this->getServer()->getName() !== "PocketMine-MP") {
            $this->getLogger()->error("Well... You're using a spoon. PIGS HATE SPOONS! So enjoy a featureless Custom Enchant plugin by Piggy until you switch to PMMP! :)");
            return true;
        }
        return false;
    }

    public function initCustomEnchants()
    {
        CustomEnchants::init();
        foreach ($this->enchants as $id => $data) {
            $ce = $this->translateDataToCE($id, $data);
            CustomEnchants::registerEnchants($id, $ce);
        }
    }

    /**
     * @param $id
     * @param $data
     * @return CustomEnchants
     */
    public function translateDataToCE($id, $data)
    {
        $slot = CustomEnchants::SLOT_NONE;
        switch ($data[1]) {
            case "Global":
                $slot = CustomEnchants::SLOT_ALL;
                break;
            case "Weapons":
                $slot = CustomEnchants::SLOT_SWORD;
                break;
            case "Bow":
                $slot = CustomEnchants::SLOT_BOW;
                break;
            case "Tools":
                $slot = CustomEnchants::SLOT_TOOL;
                break;
            case "Pickaxe":
                $slot = CustomEnchants::SLOT_PICKAXE;
                break;
            case "Axe":
                $slot = CustomEnchants::SLOT_AXE;
                break;
            case "Armor":
                $slot = CustomEnchants::SLOT_ARMOR;
                break;
            case "Helmets":
                $slot = CustomEnchants::SLOT_HEAD;
                break;
            case "Chestplate":
                $slot = CustomEnchants::SLOT_TORSO;
                break;
            case "Leggings":
                $slot = CustomEnchants::SLOT_LEGS;
                break;
            case "Boots":
                $slot = CustomEnchants::SLOT_FEET;
                break;
        }
        $rarity = CustomEnchants::RARITY_COMMON;
        switch ($data[3]) {
            case "Common":
                $rarity = CustomEnchants::RARITY_COMMON;
                break;
            case "Uncommon":
                $rarity = CustomEnchants::RARITY_UNCOMMON;
                break;
            case "Rare":
                $rarity = CustomEnchants::RARITY_RARE;
                break;
            case "Mythic":
                $rarity = CustomEnchants::RARITY_MYTHIC;
                break;
        }
        $ce = new CustomEnchants($id, $data[0], $rarity, CustomEnchants::ACTIVATION_SELF, $slot);
        return $ce;
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
     * @param $enchants
     * @param $level
     * @param Player $player
     * @param CommandSender|null $sender
     * @param null $slot
     * @param bool $check
     */
    public function addEnchantment(Item $item, $enchants, $level, Player $player, CommandSender $sender = null, $slot = null, $check = true)
    {
        //TODO: Check if item can get enchant
        if (!is_array($enchants)) {
            $enchants = [$enchants];
        }

        foreach ($enchants as $enchant) {
            $enchant = CustomEnchants::getEnchantByName($enchant);
            if ($enchant == null) {
                if ($sender !== null) {
                    $sender->sendMessage("§cInvalid enchantment.");
                }
                continue;
            }
            $result = $this->canBeEnchanted($item, $enchant, $level);
            if ($result === true || $check !== true) {
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
                continue;
            }
            if ($sender !== null) {
                if ($result == self::NOT_COMPATIBLE) {
                    $sender->sendMessage("§cThe item is not compatible with this enchant.");
                }
                if ($result == self::NOT_COMPATIBLE_WITH_OTHER_ENCHANT) {
                    $sender->sendMessage("§cThe enchant is not compatible with another enchant.");
                }
                if ($result == self::MAX_LEVEL) {
                    $sender->sendMessage("§cThe max level is " . $this->getEnchantMaxLevel($enchant) . ".");
                }
            }
            continue;
        }
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
     * @param CustomEnchants $enchant
     * @return string
     */
    public function getEnchantType(CustomEnchants $enchant)
    {
        foreach ($this->enchants as $id => $data) {
            if ($enchant->getId() == $id) {
                return $data[1];
            }
        }
        return "Unknown";
    }

    /**
     * @param CustomEnchants $enchant
     * @return int
     */
    public function getEnchantMaxLevel(CustomEnchants $enchant)
    {
        foreach ($this->enchants as $id => $data) {
            if ($enchant->getId() == $id) {
                return $data[4];
            }
        }
        return 5;
    }

    /**
     * @return array
     */
    public function sortEnchants()
    {
        $sorted = [];
        foreach ($this->enchants as $id => $data) {
            $type = $data[1];
            if (!isset($sorted[$type])) {
                $sorted[$type] = [$data[0]];
            } else {
                array_push($sorted[$type], $data[0]);
            }
        }
        return $sorted;
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
     * @param $level
     * @return bool
     */
    public function canBeEnchanted(Item $item, CustomEnchants $enchant, $level)
    {
        $type = $this->getEnchantType($enchant);
        if ($this->getEnchantMaxLevel($enchant) < $level) {
            return self::MAX_LEVEL;
        }
        if (($enchant->getId() == CustomEnchants::PORKIFIED && $this->getEnchantment($item, CustomEnchants::BLAZE) !== null) || ($enchant->getId() == CustomEnchants::BLAZE && $this->getEnchantment($item, CustomEnchants::PORKIFIED) !== null)) {
            return self::NOT_COMPATIBLE_WITH_OTHER_ENCHANT;
        }
        switch ($type) {
            case "Global":
                return true;
            case "Damageable":
                if ($item->getMaxDurability() !== 0) {
                    return true;
                }
                break;
            case "Weapons":
                if ($item->isSword() !== false || $item->isAxe() || $item->getId() == Item::BOW) {
                    return true;
                }
                break;
            case "Bow":
                if ($item->getId() == Item::BOW) {
                    return true;
                }
                break;
            case "Tools":
                if ($item->isPickaxe() || $item->isAxe() || $item->isShovel() || $item->isShears()) {
                    return true;
                }
                break;
            case "Pickaxe":
                if ($item->isPickaxe()) {
                    return true;
                }
                break;
            case "Axe":
                if ($item->isAxe()) {
                    return true;
                }
                break;
            case "Armor":
                if ($item instanceof Armor) {
                    return true;
                }
                break;
            case "Helmets":
                switch ($item->getId()) {
                    case Item::LEATHER_CAP:
                    case Item::IRON_HELMET:
                    case Item::GOLD_HELMET:
                    case Item::DIAMOND_HELMET:
                        return true;
                }
                break;
            case "Boots":
                switch ($item->getId()) {
                    case Item::LEATHER_BOOTS:
                    case Item::IRON_BOOTS:
                    case Item::GOLD_BOOTS:
                    case Item::DIAMOND_BOOTS:
                        return true;
                }
                break;
        }
        return self::NOT_COMPATIBLE;
    }
}