<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\enchants\armor\AntiKnockbackEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\ArmoredEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\AttackerDeterrentEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\BerserkerEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\boots\JetpackEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\boots\MagmaWalkerEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\boots\StompEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\CactusEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate\ChickenEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate\ParachuteEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate\ProwlEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate\SpiderEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\chestplate\VacuumEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\CloakingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\EndershiftEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\EnlightedEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\ForcefieldEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\GrowEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\HeavyEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet\AntitoxinEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet\FocusedEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet\ImplantsEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\helmet\MeditationEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\MoltenEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\OverloadEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\PoisonousCloudEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\ReviveEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\SelfDestructEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\ShieldedEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\ShrinkEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\armor\TankEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\AutoRepairEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\LuckyCharmEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\RadarEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\SoulboundEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\miscellaneous\ToggleableEffectEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\axes\LumberjackEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\DrillerEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\EnergizingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\ExplosiveEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe\FarmerEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe\FertilizerEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\hoe\HarvestEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\pickaxe\JackpotEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\QuickeningEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\SmeltingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\TelepathyEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\BlessedEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\AutoAimEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\BombardmentEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\BountyHunterEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\GrapplingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\HeadhunterEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\HealingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\MissileEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\MolotovEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\ParalyzeEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\PiercingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\ProjectileChangingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\ShuffleEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows\VolleyEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\ConditionalDamageMultiplierEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\DeathbringerEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\DeepWoundsEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\DisarmingEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\DisarmorEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\GooeyEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\HallucinationEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\LacedWeaponEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\LifestealEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\LightningEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\weapons\VampireEnchant;
use DaPigGuy\PiggyCustomEnchants\entities\HomingArrow;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyFireball;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyWitherSkull;
use DaPigGuy\PiggyCustomEnchants\entities\PigProjectile;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\utils\StringToTParser;
use ReflectionProperty;

class CustomEnchantManager
{
    private static PiggyCustomEnchants $plugin;

    /** @var CustomEnchant[] */
    public static array $enchants = [];

    public static function init(PiggyCustomEnchants $plugin): void
    {
        self::$plugin = $plugin;

        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::CURSED, "Cursed", [VanillaEffects::WITHER()], [60], [1], Rarity::UNCOMMON));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::DRUNK, "Drunk", [VanillaEffects::SLOWNESS(), VanillaEffects::MINING_FATIGUE(), VanillaEffects::NAUSEA()], [60, 60, 60], [1, 1, 0]));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::FROZEN, "Frozen", [VanillaEffects::SLOWNESS()], [60], [1]));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::HARDENED, "Hardened", [VanillaEffects::WEAKNESS()], [60], [1], Rarity::UNCOMMON));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::POISONED, "Poisoned", [VanillaEffects::POISON()], [60], [1], Rarity::UNCOMMON));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::REVULSION, "Revulsion", [VanillaEffects::NAUSEA()], [20], [0], Rarity::UNCOMMON));

        self::registerEnchantment(new ConditionalDamageMultiplierEnchant($plugin, CustomEnchantIds::AERIAL, "Aerial", fn(EntityDamageByEntityEvent $event) => !$event->getDamager()?->isOnGround(), Rarity::UNCOMMON));
        self::registerEnchantment(new ConditionalDamageMultiplierEnchant($plugin, CustomEnchantIds::BACKSTAB, "Backstab", fn(EntityDamageByEntityEvent $event) => $event->getDamager()?->getDirectionVector()->dot($event->getEntity()->getDirectionVector()) > 0, Rarity::UNCOMMON));
        self::registerEnchantment(new ConditionalDamageMultiplierEnchant($plugin, CustomEnchantIds::CHARGE, "Charge", fn(EntityDamageByEntityEvent $event) => ($damager = $event->getDamager()) instanceof Living && $damager->isSprinting(), Rarity::UNCOMMON));

        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::BLIND, "Blind", Rarity::COMMON, [VanillaEffects::BLINDNESS()], [20], [0], [100]));
        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::CRIPPLE, "Cripple", Rarity::COMMON, [VanillaEffects::NAUSEA(), VanillaEffects::SLOWNESS()], [100, 100], [0, 1]));
        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::POISON, "Poison", Rarity::UNCOMMON, [VanillaEffects::POISON()]));
        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::WITHER, "Wither", Rarity::UNCOMMON, [VanillaEffects::WITHER()]));

        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::BLAZE, "Blaze", PiggyFireball::class));
        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::HOMING, "Homing", HomingArrow::class, 3, Rarity::MYTHIC));
        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::PORKIFIED, "Porkified", PigProjectile::class, 3, Rarity::MYTHIC));
        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::WITHERSKULL, "Wither Skull", PiggyWitherSkull::class, 1, Rarity::MYTHIC));

        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::ENRAGED, "Enraged", 5, CustomEnchant::TYPE_CHESTPLATE, CustomEnchant::ITEM_TYPE_CHESTPLATE, VanillaEffects::STRENGTH(), -1));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::GEARS, "Gears", 1, CustomEnchant::TYPE_BOOTS, CustomEnchant::ITEM_TYPE_BOOTS, VanillaEffects::SPEED(), 0, 0, Rarity::UNCOMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::GLOWING, "Glowing", 1, CustomEnchant::TYPE_HELMET, CustomEnchant::ITEM_TYPE_HELMET, VanillaEffects::NIGHT_VISION(), 0, 0, Rarity::COMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::HASTE, "Haste", 5, CustomEnchant::TYPE_HAND, CustomEnchant::ITEM_TYPE_PICKAXE, VanillaEffects::HASTE(), 0, 1, Rarity::UNCOMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::OBSIDIANSHIELD, "Obsidian Shield", 1, CustomEnchant::TYPE_ARMOR_INVENTORY, CustomEnchant::ITEM_TYPE_ARMOR, VanillaEffects::FIRE_RESISTANCE(), 0, 0, Rarity::COMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::OXYGENATE, "Oxygenate", 1, CustomEnchant::TYPE_HAND, CustomEnchant::ITEM_TYPE_PICKAXE, VanillaEffects::WATER_BREATHING(), 0, 0, Rarity::UNCOMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::SPRINGS, "Springs", 1, CustomEnchant::TYPE_BOOTS, CustomEnchant::ITEM_TYPE_BOOTS, VanillaEffects::JUMP_BOOST(), 3, 0, Rarity::UNCOMMON));

        self::registerEnchantment(new AntiKnockbackEnchant($plugin, CustomEnchantIds::ANTIKNOCKBACK));
        self::registerEnchantment(new AntitoxinEnchant($plugin, CustomEnchantIds::ANTITOXIN));
        self::registerEnchantment(new AutoAimEnchant($plugin, CustomEnchantIds::AUTOAIM));
        self::registerEnchantment(new AutoRepairEnchant($plugin, CustomEnchantIds::AUTOREPAIR));
        self::registerEnchantment(new ArmoredEnchant($plugin, CustomEnchantIds::ARMORED));
        self::registerEnchantment(new BerserkerEnchant($plugin, CustomEnchantIds::BERSERKER));
        self::registerEnchantment(new BlessedEnchant($plugin, CustomEnchantIds::BLESSED));
        self::registerEnchantment(new BombardmentEnchant($plugin, CustomEnchantIds::BOMBARDMENT));
        self::registerEnchantment(new BountyHunterEnchant($plugin, CustomEnchantIds::BOUNTYHUNTER));
        self::registerEnchantment(new CactusEnchant($plugin, CustomEnchantIds::CACTUS));
        self::registerEnchantment(new ChickenEnchant($plugin, CustomEnchantIds::CHICKEN));
        self::registerEnchantment(new CloakingEnchant($plugin, CustomEnchantIds::CLOAKING));
        self::registerEnchantment(new DeathbringerEnchant($plugin, CustomEnchantIds::DEATHBRINGER));
        self::registerEnchantment(new DeepWoundsEnchant($plugin, CustomEnchantIds::DEEPWOUNDS));
        self::registerEnchantment(new DisarmingEnchant($plugin, CustomEnchantIds::DISARMING));
        self::registerEnchantment(new DisarmorEnchant($plugin, CustomEnchantIds::DISARMOR));
        self::registerEnchantment(new DrillerEnchant($plugin, CustomEnchantIds::DRILLER));
        self::registerEnchantment(new EndershiftEnchant($plugin, CustomEnchantIds::ENDERSHIFT));
        self::registerEnchantment(new EnergizingEnchant($plugin, CustomEnchantIds::ENERGIZING));
        self::registerEnchantment(new EnlightedEnchant($plugin, CustomEnchantIds::ENLIGHTED));
        self::registerEnchantment(new ExplosiveEnchant($plugin, CustomEnchantIds::EXPLOSIVE));
        self::registerEnchantment(new FarmerEnchant($plugin, CustomEnchantIds::FARMER));
        self::registerEnchantment(new FertilizerEnchant($plugin, CustomEnchantIds::FERTILIZER));
        self::registerEnchantment(new FocusedEnchant($plugin, CustomEnchantIds::FOCUSED));
        self::registerEnchantment(new ForcefieldEnchant($plugin, CustomEnchantIds::FORCEFIELD));
        self::registerEnchantment(new GooeyEnchant($plugin, CustomEnchantIds::GOOEY));
        self::registerEnchantment(new GrapplingEnchant($plugin, CustomEnchantIds::GRAPPLING));
        self::registerEnchantment(new GrowEnchant($plugin, CustomEnchantIds::GROW));
        self::registerEnchantment(new HallucinationEnchant($plugin, CustomEnchantIds::HALLUCINATION));
        self::registerEnchantment(new HarvestEnchant($plugin, CustomEnchantIds::HARVEST));
        self::registerEnchantment(new HeadhunterEnchant($plugin, CustomEnchantIds::HEADHUNTER));
        self::registerEnchantment(new HealingEnchant($plugin, CustomEnchantIds::HEALING));
        self::registerEnchantment(new HeavyEnchant($plugin, CustomEnchantIds::HEAVY));
        self::registerEnchantment(new ImplantsEnchant($plugin, CustomEnchantIds::IMPLANTS));
        self::registerEnchantment(new JackpotEnchant($plugin, CustomEnchantIds::JACKPOT));
        self::registerEnchantment(new JetpackEnchant($plugin, CustomEnchantIds::JETPACK));
        self::registerEnchantment(new LifestealEnchant($plugin, CustomEnchantIds::LIFESTEAL));
        self::registerEnchantment(new LightningEnchant($plugin, CustomEnchantIds::LIGHTNING));
        self::registerEnchantment(new LuckyCharmEnchant($plugin, CustomEnchantIds::LUCKYCHARM));
        self::registerEnchantment(new LumberjackEnchant($plugin, CustomEnchantIds::LUMBERJACK));
        self::registerEnchantment(new MagmaWalkerEnchant($plugin, CustomEnchantIds::MAGMAWALKER));
        self::registerEnchantment(new MeditationEnchant($plugin, CustomEnchantIds::MEDITATION));
        self::registerEnchantment(new MissileEnchant($plugin, CustomEnchantIds::MISSILE));
        self::registerEnchantment(new MolotovEnchant($plugin, CustomEnchantIds::MOLOTOV));
        self::registerEnchantment(new MoltenEnchant($plugin, CustomEnchantIds::MOLTEN));
        self::registerEnchantment(new OverloadEnchant($plugin, CustomEnchantIds::OVERLOAD));
        self::registerEnchantment(new ParachuteEnchant($plugin, CustomEnchantIds::PARACHUTE));
        self::registerEnchantment(new ParalyzeEnchant($plugin, CustomEnchantIds::PARALYZE));
        self::registerEnchantment(new PiercingEnchant($plugin, CustomEnchantIds::PIERCING));
        self::registerEnchantment(new PoisonousCloudEnchant($plugin, CustomEnchantIds::POISONOUSCLOUD));
        self::registerEnchantment(new ProwlEnchant($plugin, CustomEnchantIds::PROWL));
        self::registerEnchantment(new QuickeningEnchant($plugin, CustomEnchantIds::QUICKENING));
        self::registerEnchantment(new RadarEnchant($plugin, CustomEnchantIds::RADAR));
        self::registerEnchantment(new ReviveEnchant($plugin, CustomEnchantIds::REVIVE));
        self::registerEnchantment(new SelfDestructEnchant($plugin, CustomEnchantIds::SELFDESTRUCT));
        self::registerEnchantment(new ShieldedEnchant($plugin, CustomEnchantIds::SHIELDED));
        self::registerEnchantment(new ShrinkEnchant($plugin, CustomEnchantIds::SHRINK));
        self::registerEnchantment(new ShuffleEnchant($plugin, CustomEnchantIds::SHUFFLE));
        self::registerEnchantment(new SmeltingEnchant($plugin, CustomEnchantIds::SMELTING));
        self::registerEnchantment(new SoulboundEnchant($plugin, CustomEnchantIds::SOULBOUND));
        self::registerEnchantment(new SpiderEnchant($plugin, CustomEnchantIds::SPIDER));
        self::registerEnchantment(new StompEnchantment($plugin, CustomEnchantIds::STOMP));
        self::registerEnchantment(new TankEnchant($plugin, CustomEnchantIds::TANK));
        self::registerEnchantment(new TelepathyEnchant($plugin, CustomEnchantIds::TELEPATHY));
        self::registerEnchantment(new VacuumEnchant($plugin, CustomEnchantIds::VACUUM));
        self::registerEnchantment(new VampireEnchant($plugin, CustomEnchantIds::VAMPIRE));
        self::registerEnchantment(new VolleyEnchant($plugin, CustomEnchantIds::VOLLEY));
    }

    public static function getPlugin(): PiggyCustomEnchants
    {
        return self::$plugin;
    }

    public static function registerEnchantment(CustomEnchant $enchant): void
    {
        EnchantmentIdMap::getInstance()->register($enchant->getId(), $enchant);
        self::$enchants[$enchant->getId()] = $enchant;
        StringToEnchantmentParser::getInstance()->register($enchant->name, fn() => $enchant);
        if ($enchant->name !== $enchant->getDisplayName()) StringToEnchantmentParser::getInstance()->register($enchant->getDisplayName(), fn() => $enchant);

        self::$plugin->getLogger()->debug("Custom Enchantment '" . $enchant->getDisplayName() . "' registered with id " . $enchant->getId());
    }

    public static function unregisterEnchantment(int|CustomEnchant $id): void
    {
        $id = $id instanceof CustomEnchant ? $id->getId() : $id;
        $enchant = self::$enchants[$id];

        $property = new ReflectionProperty(StringToTParser::class, "callbackMap");
        $property->setAccessible(true);
        $value = $property->getValue(StringToEnchantmentParser::getInstance());
        unset($value[strtolower(str_replace([" ", "minecraft:"], ["_", ""], trim($enchant->name)))]);
        if ($enchant->name !== $enchant->getDisplayName()) unset($value[strtolower(str_replace([" ", "minecraft:"], ["_", ""], trim($enchant->getDisplayName())))]);
        $property->setValue(StringToEnchantmentParser::getInstance(), $value);

        self::$plugin->getLogger()->debug("Custom Enchantment '" . $enchant->getDisplayName() . "' unregistered with id " . $enchant->getId());
        unset(self::$enchants[$id]);

        $property = new ReflectionProperty(EnchantmentIdMap::class, "enumToId");
        $property->setAccessible(true);
        $value = $property->getValue(EnchantmentIdMap::getInstance());
        unset($value[spl_object_id(EnchantmentIdMap::getInstance()->fromId($id))]);
        $property->setValue(EnchantmentIdMap::getInstance(), $value);

        $property = new ReflectionProperty(EnchantmentIdMap::class, "idToEnum");
        $property->setAccessible(true);
        $value = $property->getValue(EnchantmentIdMap::getInstance());
        unset($value[$id]);
        $property->setValue(EnchantmentIdMap::getInstance(), $value);
    }

    /**
     * @return CustomEnchant[]
     */
    public static function getEnchantments(): array
    {
        return self::$enchants;
    }

    public static function getEnchantment(int $id): ?CustomEnchant
    {
        return self::$enchants[$id] ?? null;
    }

    public static function getEnchantmentByName(string $name): ?CustomEnchant
    {
        return ($enchant = StringToEnchantmentParser::getInstance()->parse($name)) instanceof CustomEnchant ? $enchant : null;
    }
}
