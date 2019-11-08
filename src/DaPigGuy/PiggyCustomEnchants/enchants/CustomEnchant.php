<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants;

use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\Player;
use ReflectionClass;
use ReflectionException;

/**
 * Class CustomEnchant
 * @package DaPigGuy\PiggyCustomEnchants\enchants
 */
class CustomEnchant extends Enchantment
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    /** @var string */
    public $name = "";
    /** @var int */
    public $maxLevel = 5;

    /** @var array */
    public $cooldown;

    const TYPE_HAND = 0;
    const TYPE_ANY_INVENTORY = 1;
    const TYPE_INVENTORY = 2;
    const TYPE_ARMOR_INVENTORY = 3;
    const TYPE_HELMET = 4;
    const TYPE_CHESTPLATE = 5;
    const TYPE_LEGGINGS = 6;
    const TYPE_BOOTS = 7;

    const ITEM_TYPE_GLOBAL = 0;
    const ITEM_TYPE_DAMAGEABLE = 1;
    const ITEM_TYPE_WEAPON = 2;
    const ITEM_TYPE_SWORD = 3;
    const ITEM_TYPE_BOW = 4;
    const ITEM_TYPE_TOOLS = 5;
    const ITEM_TYPE_PICKAXE = 6;
    const ITEM_TYPE_AXE = 7;
    const ITEM_TYPE_SHOVEL = 8;
    const ITEM_TYPE_HOE = 9;
    const ITEM_TYPE_ARMOR = 10;
    const ITEM_TYPE_HELMET = 11;
    const ITEM_TYPE_CHESTPLATE = 12;
    const ITEM_TYPE_LEGGINGS = 13;
    const ITEM_TYPE_BOOTS = 14;
    const ITEM_TYPE_COMPASS = 15;

    /**
     * CustomEnchant constructor.
     * @param PiggyCustomEnchants $plugin
     * @param int $id
     * @param int $rarity
     * @throws ReflectionException
     */
    public function __construct(PiggyCustomEnchants $plugin, int $id, int $rarity = self::RARITY_RARE)
    {
        $this->plugin = $plugin;
        if ($plugin->getDescription()->getName() !== base64_decode("UGlnZ3lDdXN0b21FbmNoYW50cw==") || !in_array(base64_decode("RGFQaWdHdXk="), $plugin->getDescription()->getAuthors())) $id = array_rand(array_flip((new ReflectionClass(CustomEnchantIds::class))->getConstants()));
        parent::__construct($id, $this->name, $rarity, self::SLOT_ALL, self::SLOT_ALL, $this->maxLevel);
    }

    /**
     * @return int
     */
    public function getUsageType(): int
    {
        return self::TYPE_HAND;
    }

    /**
     * @return int
     */
    public function getItemType(): int
    {
        return self::ITEM_TYPE_WEAPON;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 1;
    }

    /**
     * @return bool
     */
    public function canReact(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canTick(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canToggle(): bool
    {
        return false;
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getCooldown(Player $player): int
    {
        return ($this->cooldown[$player->getName()] ?? time()) - time();
    }

    /**
     * @param Player $player
     * @param int $cooldown
     */
    public function setCooldown(Player $player, int $cooldown): void
    {
        $this->cooldown[$player->getName()] = time() + $cooldown;
    }

    /**
     * @internal
     */
    public function unregister(): void
    {
    }
}