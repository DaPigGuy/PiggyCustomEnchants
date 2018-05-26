<?php

namespace PiggyCustomEnchants\CustomEnchants;

use pocketmine\item\enchantment\Enchantment;

/**
 * Class CustomEnchants
 * @package PiggyCustomEnchants\CustomEnchants
 */
class CustomEnchants extends Enchantment
{
    public static function init(): void
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
    public static function getEnchantmentByName(string $name): ?Enchantment
    {
        $const = CustomEnchantsIds::class . "::" . strtoupper($name);
        if (defined($const)) {
            return self::getEnchantment(constant($const));
        }
        return null;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function unregisterEnchantment($id)
    {
        if (isset(parent::$enchantments[$id])) {
            unset(parent::$enchantments[$id]);
            return true;
        }
        return false;
    }
}