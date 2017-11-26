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
    const HALLUCINATION = 119;
    const BLESSED = 120;

    //TOOLS
    const EXPLOSIVE = 200; //Not accurate
    const SMELTING = 201;
    const ENERGIZING = 202;
    const QUICKENING = 203;
    const LUMBERJACK = 204;
    const TELEPATHY = 205;
    const DRILLER = 206;
    const HASTE = 207;
    const FERTILIZER = 208;
    const FARMER = 209;
    const HARVEST = 210;
    const OXYGENATE = 211;

    //BOWS
    /*const BOMBARDMENT = 300;
    const LIGHTNING = 301;
    const FIREWORK = 302;*/
    const PARALYZE = 303;
    const MOLOTOV = 304; //Falling sand fire doesn't appear
    const VOLLEY = 305;
    //WITHER SHOULD BE HERE AGAIN... BUT...
    const PIERCING = 307;
    const SHUFFLE = 308;
    const BOUNTYHUNTER = 309; //Not accurate
    const HEALING = 310;
    const BLAZE = 311;
    const HEADHUNTER = 312;
    const GRAPPLING = 313;
    const PORKIFIED = 314;
    const MISSILE = 315;

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
    const SHRINK = 414;
    const GROW = 415;
    const CACTUS = 416;
    const ANTIKNOCKBACK = 417;
    const FORCEFIELD = 418;
    const OVERLOAD = 419;
    const ARMORED = 420;
    const TANK = 421;
    const HEAVY = 422;

    //HELMET
    const IMPLANTS = 600;
    const GLOWING = 601;
    const MEDITATION = 602;

    //CHESTPLATE
    const PARACHUTE = 800;
    const CHICKEN = 801;
    const PROWL = 802;
    const SPIDER = 803;
    const ENRAGED = 804;

    //BOOTS
    const GEARS = 500;
    const SPRINGS = 501;
    const STOMP = 502;
    const JETPACK = 503;
    const MAGMAWALKER = 504;

    //COMPASS
    const RADAR = 700;

    const INVALID = -1;

    const SLOT_COMPASS = 0b10000000000000;

    public static $enchantments;

    /**
     * @param $id
     * @param CustomEnchants $enchant
     */
    public static function registerEnchants($id, CustomEnchants $enchant)
    {
        self::$enchantments[$id] = $enchant;
    }

    /**
     * @param int $id
     * @return CustomEnchants
     */
    public static function getEnchantment(int $id)
    {
        if (isset(self::$enchantments[$id])) {
            return clone self::$enchantments[$id];
        }
        return null;
    }

    /**
     * @param $name
     * @return null|CustomEnchants
     */
    public static function getEnchantByName(string $name)
    {
        if (defined(CustomEnchants::class . "::" . strtoupper($name))) {
            return self::getEnchantment(constant(CustomEnchants::class . "::" . strtoupper($name)));
        }
        return null;
    }


}