<?php

namespace PiggyCustomEnchants\CustomEnchants;

use pocketmine\item\enchantment\Enchantment;

/**
 * Class CustomEnchants
 * @package PiggyCustomEnchants\CustomEnchants
 */
class CustomEnchants extends Enchantment
{
    public static function init()
    {
        $vanillaenchants = [];
        foreach (parent::$enchantments as $id => $enchantment) {
            $vanillaenchants[$id] = $enchantment;
        }
        parent::$enchantments = $vanillaenchants;
    }

    /**
     * @param string $name
     *
     * @return Enchantment|null
     */
    public static function getEnchantmentByName(string $name){
        $const = CustomEnchantsIds::class . "::" . strtoupper($name);
        if(defined($const)){
            return self::getEnchantment(constant($const));
        }
        return null;
    }
}