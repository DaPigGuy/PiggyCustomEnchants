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
    const GOOEY = 103;
    const POISON = 104;
    /*const BLOCK = 105;
    const ICEASPECT = 106;
    const SHOCKWAVE = 107;*/
    const AUTOREPAIR = 108;
    const CRIPPLE = 109;
    const CRIPPLINGSTRIKE = 109;
    //const THUNDERBLOW = 110;
    const VAMPIRE = 111;
    //const DEEPWOUNDS = 112;
    const CHARGE = 113;
    const AERIAL = 114;
    const WITHER = 115;
    //const HEADLESS = 116;
    const DISARMING = 117;
    const SOULBOUND = 118;

    //TOOLS
    const EXPLOSIVE = 200; //Not accurate
    const SMELTING = 201;
    const ENERGIZING = 202;
    const QUICKENING = 203;
    const LUMBERJACK = 204;
    const TELEPATHY = 205;

    //BOWS
    /*const BOMBARDMENT = 300;
    const LIGHTNING = 301;
    const FIREWORK = 302;
    const PARALYZE = 303;*/
    const MOLOTOV = 304; //Falling sand fire doesn't appear
    const VOLLEY = 305;
    //WITHER SHOULD BE HERE AGAIN... BUT...
    const PIERCING = 307;
    const SHUFFLE = 308;
    //const BOUNTYHUNTER = 309;
    const HEALING = 310;
    const BLAZE = 311;
    const HEADHUNTER = 312;
    const GRAPPLING = 313;
    const PORKIFIED = 314;


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
    const REVIVE = 413;


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

    /**
     * @param $id
     * @param CustomEnchants $enchant
     */
    public static function registerEnchants($id, CustomEnchants $enchant){
        self::$enchantments[$id] = $enchant;
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