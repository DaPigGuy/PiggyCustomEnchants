<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\utils;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Bow;
use pocketmine\item\Compass;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class Utils
 * @package DaPigGuy\PiggyCustomEnchants\utils
 */
class Utils
{
    const TYPE_NAMES = [
        CustomEnchant::ITEM_TYPE_ARMOR => "Armor",
        CustomEnchant::ITEM_TYPE_HELMET => "Helmet",
        CustomEnchant::ITEM_TYPE_CHESTPLATE => "Chestplate",
        CustomEnchant::ITEM_TYPE_LEGGINGS => "Leggings",
        CustomEnchant::ITEM_TYPE_BOOTS => "Boots",
        CustomEnchant::ITEM_TYPE_WEAPON => "Weapon",
        CustomEnchant::ITEM_TYPE_SWORD => "Sword",
        CustomEnchant::ITEM_TYPE_BOW => "Bow",
        CustomEnchant::ITEM_TYPE_TOOLS => "Tools",
        CustomEnchant::ITEM_TYPE_PICKAXE => "Pickaxe",
        CustomEnchant::ITEM_TYPE_AXE => "Axe",
        CustomEnchant::ITEM_TYPE_SHOVEL => "Shovel",
        CustomEnchant::ITEM_TYPE_HOE => "Hoe",
        CustomEnchant::ITEM_TYPE_DAMAGEABLE => "Damageable",
        CustomEnchant::ITEM_TYPE_GLOBAL => "Global",
        CustomEnchant::ITEM_TYPE_COMPASS => "Compass",
    ];
    const RARITY_NAMES = [
        CustomEnchant::RARITY_COMMON => "Common",
        CustomEnchant::RARITY_UNCOMMON => "Uncommon",
        CustomEnchant::RARITY_RARE => "Rare",
        CustomEnchant::RARITY_MYTHIC => "Mythic"
    ];

    const INCOMPATIBLE_ENCHANTS = [
        CustomEnchantIds::BLAZE => [CustomEnchantIds::PORKIFIED, CustomEnchantIds::WITHERSKULL],
        CustomEnchantIds::GRAPPLING => [CustomEnchantIds::VOLLEY],
        CustomEnchantIds::GROW => [CustomEnchantIds::SHRINK],
        CustomEnchantIds::PORKIFIED => [CustomEnchantIds::WITHERSKULL]
    ];

    /** @var array */
    public static $shouldTakeFallDamage;

    /** @var array */
    public static $exemptFromReactions;

    /**
     * @param int $integer
     * @return string
     */
    public static function getRomanNumeral(int $integer)
    {
        $romanString = "";
        while ($integer > 0) {
            $romanNumeralConversionTable = [
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1
            ];
            foreach ($romanNumeralConversionTable as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $romanString .= $rom;
                    break;
                }
            }
        }
        return $romanString;
    }

    /**
     * @param Item $item
     * @return bool
     */
    public static function isHelmet(Item $item): bool
    {
        return in_array($item->getId(), [Item::LEATHER_CAP, Item::CHAIN_HELMET, Item::IRON_HELMET, Item::GOLD_HELMET, Item::DIAMOND_HELMET]);
    }

    /**
     * @param Item $item
     * @return bool
     */
    public static function isChestplate(Item $item): bool
    {
        return in_array($item->getId(), [Item::LEATHER_TUNIC, Item::CHAIN_CHESTPLATE, Item::IRON_CHESTPLATE, Item::GOLD_CHESTPLATE, Item::DIAMOND_CHESTPLATE]);
    }

    /**
     * @param Item $item
     * @return bool
     */
    public static function isLeggings(Item $item): bool
    {
        return in_array($item->getId(), [Item::LEATHER_PANTS, Item::CHAIN_LEGGINGS, Item::IRON_LEGGINGS, Item::GOLD_LEGGINGS, Item::DIAMOND_LEGGINGS]);
    }

    /**
     * @param Item $item
     * @return bool
     */
    public static function isBoots(Item $item): bool
    {
        return in_array($item->getId(), [Item::LEATHER_BOOTS, Item::CHAIN_BOOTS, Item::IRON_BOOTS, Item::GOLD_BOOTS, Item::DIAMOND_BOOTS]);
    }

    /**
     * @param Item $item
     * @param int $itemType
     * @return bool
     */
    public static function itemMatchesItemType(Item $item, int $itemType)
    {
        switch ($itemType) {
            case CustomEnchant::ITEM_TYPE_GLOBAL:
                return true;
            case CustomEnchant::ITEM_TYPE_DAMAGEABLE:
                return $item instanceof Durable;
            case CustomEnchant::ITEM_TYPE_WEAPON:
                return $item instanceof Sword || $item instanceof Axe || $item instanceof Bow;
            case CustomEnchant::ITEM_TYPE_SWORD:
                return $item instanceof Sword;
            case CustomEnchant::ITEM_TYPE_BOW:
                return $item instanceof Bow;
            case CustomEnchant::ITEM_TYPE_TOOLS:
                return $item instanceof Pickaxe || $item instanceof Axe || $item instanceof Shovel || $item instanceof Hoe || $item instanceof Shears;
            case CustomEnchant::ITEM_TYPE_PICKAXE:
                return $item instanceof Pickaxe;
            case CustomEnchant::ITEM_TYPE_AXE:
                return $item instanceof Axe;
            case CustomEnchant::ITEM_TYPE_SHOVEL:
                return $item instanceof Shovel;
            case CustomEnchant::ITEM_TYPE_HOE:
                return $item instanceof Hoe;
            case CustomEnchant::ITEM_TYPE_ARMOR:
                return $item instanceof Armor;
            case CustomEnchant::ITEM_TYPE_HELMET:
                return self::isHelmet($item);
            case CustomEnchant::ITEM_TYPE_CHESTPLATE:
                return self::isChestplate($item);
            case CustomEnchant::ITEM_TYPE_LEGGINGS:
                return self::isLeggings($item);
            case CustomEnchant::ITEM_TYPE_BOOTS:
                return self::isBoots($item);
            case CustomEnchant::ITEM_TYPE_COMPASS:
                return $item instanceof Compass;
        }
        return false;
    }

    /**
     * @param Item $item
     * @param CustomEnchant $enchant
     * @return bool
     */
    public static function checkEnchantIncompatibilities(Item $item, CustomEnchant $enchant): bool
    {
        foreach ($item->getEnchantments() as $enchantment) {
            if (isset(self::INCOMPATIBLE_ENCHANTS[$enchantment->getId()]) && in_array($enchant->getId(), self::INCOMPATIBLE_ENCHANTS[$enchantment->getId()])) return false;
            if (isset(self::INCOMPATIBLE_ENCHANTS[$enchant->getId()]) && in_array($enchantment->getId(), self::INCOMPATIBLE_ENCHANTS[$enchant->getId()])) return false;
        }
        return true;
    }

    /**
     * @param Item $item
     * @return Item
     */
    public static function displayEnchants(Item $item): Item
    {
        $plugin = CustomEnchantManager::getPlugin();
        if (count($item->getEnchantments()) > 0) {
            $additionalInformation = $plugin->getConfig()->getNested("enchants.position") === "name" ? TextFormat::RESET . TextFormat::WHITE . $item->getName() : "";
            foreach ($item->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant) {
                    $additionalInformation .= "\n" . TextFormat::RESET . Utils::getColorFromRarity($enchantment->getRarity()) . $enchantment->getName() . " " . ($plugin->getConfig()->getNested("enchants.roman-numerals") ? Utils::getRomanNumeral($enchantmentInstance->getLevel()) : $enchantmentInstance->getLevel());
                }
            }
            if ($item->getNamedTagEntry(Item::TAG_DISPLAY) instanceof CompoundTag) $item->setNamedTagEntry(new CompoundTag("OriginalDisplayTag", $item->getNamedTagEntry(Item::TAG_DISPLAY)->getValue()));
            if (CustomEnchantManager::getPlugin()->getConfig()->getNested("enchants.position") === "lore") {
                $lore = array_merge(explode("\n", $additionalInformation), $item->getLore());
                array_shift($lore);
                $item = $item->setLore($lore);
            } else {
                $item = $item->setCustomName($additionalInformation);
            }
        }
        if (CustomEnchantManager::getPlugin()->getDescription()->getName() !== "PiggyCustomEnchants" || !in_array("DaPigGuy", CustomEnchantManager::getPlugin()->getDescription()->getAuthors())) $item->setNamedTagEntry(new StringTag("LolGetRekted", "Loser"));
        return $item;
    }

    /**
     * @param Item $item
     * @return Item
     */
    public static function filterDisplayedEnchants(Item $item): Item
    {
        if ($item->getNamedTagEntry("OriginalDisplayTag") instanceof CompoundTag) {
            $item->removeNamedTagEntry(Item::TAG_DISPLAY);
            $item->setNamedTagEntry(new CompoundTag(Item::TAG_DISPLAY, $item->getNamedTagEntry("OriginalDisplayTag")->getValue()));
            $item->removeNamedTagEntry("OriginalDisplayTag");
        }
        return $item;
    }

    /**
     * @param EnchantmentInstance[] $enchantments
     * @return EnchantmentInstance[]
     */
    public static function sortEnchantmentsByPriority(array $enchantments): array
    {
        usort($enchantments, function (EnchantmentInstance $enchantmentInstance, EnchantmentInstance $enchantmentInstanceB) {
            $type = $enchantmentInstance->getType();
            $typeB = $enchantmentInstanceB->getType();
            return ($typeB instanceof CustomEnchant ? $typeB->getPriority() : 1) - ($type instanceof CustomEnchant ? $type->getPriority() : 1);
        });
        return $enchantments;
    }

    /**
     * @param int $rarity
     * @return string
     */
    public static function getColorFromRarity(int $rarity)
    {
        return self::getTFConstFromString(CustomEnchantManager::getPlugin()->getConfig()->get("rarity-colors")[strtolower(self::RARITY_NAMES[$rarity])]);
    }

    /**
     * @param string $color
     * @return string
     */
    public static function getTFConstFromString(string $color)
    {
        $colorConversionTable = [
            "BLACK" => TextFormat::BLACK,
            "DARK_BLUE" => TextFormat::DARK_BLUE,
            "DARK_GREEN" => TextFormat::DARK_GREEN,
            "DARK_AQUA" => TextFormat::DARK_AQUA,
            "DARK_RED" => TextFormat::DARK_RED,
            "DARK_PURPLE" => TextFormat::DARK_PURPLE,
            "GOLD" => TextFormat::GOLD,
            "GRAY" => TextFormat::GRAY,
            "DARK_GRAY" => TextFormat::DARK_GRAY,
            "BLUE" => TextFormat::BLUE,
            "GREEN" => TextFormat::GREEN,
            "AQUA" => TextFormat::AQUA,
            "RED" => TextFormat::RED,
            "LIGHT_PURPLE" => TextFormat::LIGHT_PURPLE,
            "YELLOW" => TextFormat::YELLOW,
            "WHITE" => TextFormat::WHITE
        ];
        return $colorConversionTable[strtoupper($color)] ?? TextFormat::GRAY;
    }

    /**
     * @param Player $player
     * @param string $error
     */
    public static function errorForm(Player $player, string $error): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if (!is_null($data)) {
                $player->getServer()->dispatchCommand($player, "ce");
                return;
            }
        });
        $form->setTitle(TextFormat::RED . "Error");
        $form->setContent($error);
        $form->addButton(TextFormat::BOLD . "Back");
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function shouldTakeFallDamage(Player $player): bool
    {
        return !isset(self::$shouldTakeFallDamage[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param bool $shouldTakeFallDamage
     * @param int $duration
     */
    public static function setShouldTakeFallDamage(Player $player, bool $shouldTakeFallDamage, int $duration = 1): void
    {
        unset(self::$shouldTakeFallDamage[$player->getName()]);
        if (!$shouldTakeFallDamage) self::$shouldTakeFallDamage[$player->getName()] = time() + $duration;
    }

    /**
     * @param Player $player
     * @return int|mixed
     */
    public static function getNoFallDamageDuration(Player $player)
    {
        return (self::$shouldTakeFallDamage[$player->getName()] ?? time()) - time();
    }

    /**
     * @param Player $player
     * @param int $duration
     */
    public static function increaseNoFallDamageDuration(Player $player, int $duration = 1): void
    {
        self::$shouldTakeFallDamage[$player->getName()] += $duration;
    }

    /**
     * @param Item $item
     * @param Enchantment $enchant
     * @param int $level
     * @return bool
     */
    public static function canBeEnchanted(Item $item, Enchantment $enchant, int $level): bool
    {
        return ((!$enchant instanceof CustomEnchant || self::itemMatchesItemType($item, $enchant->getItemType())) &&
            $level <= $enchant->getMaxLevel() &&
            (($enchantmentInstance = $item->getEnchantment($enchant->getId())) === null || $enchantmentInstance->getLevel() < $level) &&
            $item->getCount() === 1 &&
            (!$enchant instanceof CustomEnchant || self::checkEnchantIncompatibilities($item, $enchant))
        );
    }
}