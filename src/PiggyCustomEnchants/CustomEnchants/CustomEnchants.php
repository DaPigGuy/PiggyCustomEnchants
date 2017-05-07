<?php

namespace PiggyCustomEnchants\CustomEnchants;

use pocketmine\item\enchantment\Enchantment;

/**
 * Class CustomEnchants
 * @package PiggyCustomEnchants\CustomEnchants
 */
class CustomEnchants extends Enchantment
{
    //GLOBAL
    const LIFESTEAL = 100;
    const BLIND = 101;
    const DEATHBRINGER = 102;
    //const GOOEY = 103;
    const POISON = 104;
    /*const BLOCK = 105;
    const ICEASPECT = 106;
    const SHOCKWAVE = 107;
    const AUTOREPAIR = 108;*/
    const CRIPPLINGSTRIKE = 109;
    //const THUNDERBLOW = 110;
    const VAMPIRE = 111;
    //const DEEPWOUNDS = 112;
    const CHARGE = 113;
    //const AERIAL = 114;
    const WITHER = 115;
    //const HEADLESS = 116;
    const DISARMING = 117;

    //TOOLS
    const EXPLOSIVE = 200;
    const SMELTING = 201;
    const ENERGIZING = 202;
    const QUICKENING = 203;

    //BOWS
    /*const BOMBARDMENT = 300;
    const LIGHTNING = 301;
    const FIREWORK = 302;
    const PARALYZE = 303;*/
    const MOLOTOV = 304;
    const VOLLEY = 305;
    //WITHER SHOULD BE HERE AGAIN... BUT...
    const PIERCING = 307;
    const SHUFFLE = 308;
    //const BOUNTYHUNTER = 309;
    const HEALING = 310; //NO ONE WANTS YOU, SHOO!
    //const BLAZE = 311;


    //ARMOR
    const MOLTEN = 400;
    const ENLIGHTED = 401;
    const HARDENED = 402;
    const POISONED = 403;
    const FROZEN = 404;
    const OBSIDIANSHIELD = 405;
    const REVULSION = 406;
    const SELFDESTRUCT = 407;
    const CURSED = 408;
    const ENDERSHIFT = 409;
    const DRUNK = 410;
    const BERSERKER = 411;
    const CLOAKING = 412;


    //BOOTS
    const GEARS = 500;
    const SPRINGS = 501;
    const STOMP = 502;

    //HELMET
    //const IMPLANTS = 600;
    const GLOWING = 601;

    public $id;
    public $level = 1;
    public $name;
    public $rarity;
    public $activationType;
    public $slot;

    public static $enchantments;


    public static function init()
    {
        self::$enchantments[self::LIFESTEAL] = new CustomEnchants(self::LIFESTEAL, "Lifesteal", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::BLIND] = new CustomEnchants(self::BLIND, "Blind", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::DEATHBRINGER] = new CustomEnchants(self::DEATHBRINGER, "Death Bringer", self::RARITY_MYTHIC, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::POISON] = new CustomEnchants(self::POISON, "Poison", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::CRIPPLINGSTRIKE] = new CustomEnchants(self::CRIPPLINGSTRIKE, "Cripple", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::VAMPIRE] = new CustomEnchants(self::VAMPIRE, "Vampire", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::CHARGE] = new CustomEnchants(self::CHARGE, "Charge", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::WITHER] = new CustomEnchants(self::WITHER, "Wither", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::DISARMING] = new CustomEnchants(self::DISARMING, "Disarming", self::RARITY_MYTHIC, self::ACTIVATION_HELD, self::SLOT_SWORD);

        self::$enchantments[self::EXPLOSIVE] = new CustomEnchants(self::EXPLOSIVE, "Explosive", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
        self::$enchantments[self::SMELTING] = new CustomEnchants(self::SMELTING, "Smelting", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
        self::$enchantments[self::ENERGIZING] = new CustomEnchants(self::ENERGIZING, "Energizing", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
        self::$enchantments[self::QUICKENING] = new CustomEnchants(self::QUICKENING, "Quickening", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_PICKAXE);

        self::$enchantments[self::MOLOTOV] = new CustomEnchants(self::MOLOTOV, "Molotov", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_BOW);
        self::$enchantments[self::VOLLEY] = new CustomEnchants(self::VOLLEY, "Volley", self::RARITY_MYTHIC, self::ACTIVATION_HELD, self::SLOT_BOW);
        self::$enchantments[self::PIERCING] = new CustomEnchants(self::PIERCING, "Piercing", self::RARITY_MYTHIC, self::ACTIVATION_HELD, self::SLOT_BOW);
        self::$enchantments[self::SHUFFLE] = new CustomEnchants(self::SHUFFLE, "Shuffle", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_BOW);
        self::$enchantments[self::HEALING] = new CustomEnchants(self::HEALING, "Healing", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_BOW);

        self::$enchantments[self::MOLTEN] = new CustomEnchants(self::MOLTEN, "Molten", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::ENLIGHTED] = new CustomEnchants(self::ENLIGHTED, "Enlighted", self::RARITY_MYTHIC, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::HARDENED] = new CustomEnchants(self::HARDENED, "Hardened", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::POISONED] = new CustomEnchants(self::POISONED, "Poisoned", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::FROZEN] = new CustomEnchants(self::FROZEN, "Frozen", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::OBSIDIANSHIELD] = new CustomEnchants(self::OBSIDIANSHIELD, "Obsidian Shield", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::REVULSION] = new CustomEnchants(self::REVULSION, "Revulsion", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::SELFDESTRUCT] = new CustomEnchants(self::SELFDESTRUCT, "Self Destruct", self::RARITY_MYTHIC, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::CURSED] = new CustomEnchants(self::CURSED, "Cursed", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::ENDERSHIFT] = new CustomEnchants(self::ENDERSHIFT, "Endershift", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::DRUNK] = new CustomEnchants(self::DRUNK, "Drunk", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::BERSERKER] = new CustomEnchants(self::BERSERKER, "Berserker", self::RARITY_MYTHIC, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);
        self::$enchantments[self::CLOAKING] = new CustomEnchants(self::CLOAKING, "Cloaking", self::RARITY_UNCOMMON, self::ACTIVATION_EQUIP, self::SLOT_ARMOR);

        self::$enchantments[self::GEARS] = new CustomEnchants(self::GEARS, "Gears", self::RARITY_UNCOMMON, self::ACTIVATION_EQUIP, self::SLOT_HEAD);
        self::$enchantments[self::SPRINGS] = new CustomEnchants(self::SPRINGS, "Springs", self::RARITY_UNCOMMON, self::ACTIVATION_EQUIP, self::SLOT_HEAD);
        self::$enchantments[self::STOMP] = new CustomEnchants(self::STOMP, "Stomp", self::RARITY_RARE, self::ACTIVATION_EQUIP, self::SLOT_HEAD);

        self::$enchantments[self::GLOWING] = new CustomEnchants(self::GLOWING, "Glowing", self::RARITY_UNCOMMON, self::ACTIVATION_EQUIP, self::SLOT_HEAD);
    }

    /**
     * CustomEnchants constructor.
     * @param $id
     * @param $name
     * @param $rarity
     * @param $activationType
     * @param $slot
     */
    public function __construct($id, $name, $rarity, $activationType, $slot)
    {
        $this->id = $id;
        $this->name = (string)$name;
        $this->rarity = (int)$rarity;
        $this->activationType = (int)$activationType;
        $this->slot = (int)$slot;
    }

    /**
     * @param int $id
     * @return CustomEnchants
     */
    public static function getEnchantment($id)
    {
        if (isset(self::$enchantments[$id])) {
            return clone self::$enchantments[(int)$id];
        }
        return new CustomEnchants(self::TYPE_INVALID, "unknown", 0, 0, 0);
    }

    /**
     * @param $name
     * @return $this|null|CustomEnchants
     */
    public static function getEnchantByName($name)
    {
        if (defined(CustomEnchants::class . "::" . strtoupper($name))) {
            return self::getEnchantment(constant(CustomEnchants::class . "::" . strtoupper($name)));
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getRarity()
    {
        return $this->rarity;
    }

    /**
     * @return int
     */
    public function getActivationType()
    {
        return $this->activationType;
    }

    /**
     * @return int
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @param $slot
     * @return bool
     */
    public function hasSlot($slot)
    {
        return ($this->slot & $slot) > 0;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = (int)$level;

        return $this;
    }


}