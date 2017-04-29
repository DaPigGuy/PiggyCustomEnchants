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
    //const BLOCK = 105;
    //const ICEASPECT = 106;
    //const SHOCKWAVE = 107;
    //const AUTOREPAIR = 108;
    //const CRIPPLINGSTRIKE = 109;
    //const THUNDERBLOW = 110;
    //const VAMPIRE = 111;
    //const DEEPWOUNDS = 112;
    const CHARGE = 113;
    //const AERIAL = 114;
    //const WITHER = 115;
    //const HEADLESS = 116;
    const DISARMING = 117;

    //TOOLS
    const EXPLOSIVE = 200;
    const SMELTING = 201;
    const ENERGIZING = 202;
    const QUICKENING = 203;

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
        self::$enchantments[self::CHARGE] = new CustomEnchants(self::CHARGE, "Charge", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_SWORD);
        self::$enchantments[self::DISARMING] = new CustomEnchants(self::DISARMING, "Disarming", self::RARITY_MYTHIC, self::ACTIVATION_HELD, self::SLOT_SWORD);

        self::$enchantments[self::EXPLOSIVE] = new CustomEnchants(self::EXPLOSIVE, "Explosive", self::RARITY_RARE, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
        self::$enchantments[self::SMELTING] = new CustomEnchants(self::SMELTING, "Smelting", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
        self::$enchantments[self::ENERGIZING] = new CustomEnchants(self::ENERGIZING, "Energizing", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
        self::$enchantments[self::QUICKENING] = new CustomEnchants(self::QUICKENING, "Quickening", self::RARITY_UNCOMMON, self::ACTIVATION_HELD, self::SLOT_PICKAXE);
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
    public function getName()
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