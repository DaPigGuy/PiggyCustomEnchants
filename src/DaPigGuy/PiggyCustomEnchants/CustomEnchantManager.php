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
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Enchantment;
use ReflectionException;
use ReflectionProperty;

/**
 * Class CustomEnchantManager
 * @package DaPigGuy\PiggyCustomEnchants
 */
class CustomEnchantManager
{
    /** @var PiggyCustomEnchants */
    private static $plugin;

    /** @var CustomEnchant[] */
    public static $enchants = [];

    /**
     * @param PiggyCustomEnchants $plugin
     * @throws ReflectionException
     */
    public static function init(PiggyCustomEnchants $plugin)
    {
        self::$plugin = $plugin;
        $vanillaEnchantments = new \SplFixedArray(1024);

        $property = new ReflectionProperty(Enchantment::class, "enchantments");
        $property->setAccessible(true);
        foreach ($property->getValue() as $key => $value) {
            $vanillaEnchantments[$key] = $value;
        }
        $property->setValue($vanillaEnchantments);

        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::CURSED, "Cursed", [Effect::WITHER], [60], [1], CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::DRUNK, "Drunk", [Effect::SLOWNESS, Effect::MINING_FATIGUE, Effect::NAUSEA], [60, 60, 60], [1, 1, 0]));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::FROZEN, "Frozen", [Effect::SLOWNESS], [60], [1]));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::HARDENED, "Hardened", [Effect::WEAKNESS], [60], [1], CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::POISONED, "Poisoned", [Effect::POISON], [60], [1], CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new AttackerDeterrentEnchant($plugin, CustomEnchantIds::REVULSION, "Revulsion", [Effect::NAUSEA], [20], [0], CustomEnchant::RARITY_UNCOMMON));

        self::registerEnchantment(new ConditionalDamageMultiplierEnchant($plugin, CustomEnchantIds::AERIAL, "Aerial", function (EntityDamageByEntityEvent $event) {
            return $event->getDamager()->isOnGround();
        }, CustomEnchant::RARITY_COMMON));
        self::registerEnchantment(new ConditionalDamageMultiplierEnchant($plugin, CustomEnchantIds::BACKSTAB, "Backstab", function (EntityDamageByEntityEvent $event) {
            return $event->getDamager()->getDirectionVector()->dot($event->getEntity()->getDirectionVector()) > 0;
        }, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ConditionalDamageMultiplierEnchant($plugin, CustomEnchantIds::CHARGE, "Charge", function (EntityDamageByEntityEvent $event) {
            return $event->getDamager()->isSprinting();
        }, CustomEnchant::RARITY_UNCOMMON));

        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::BLIND, "Blind", CustomEnchant::RARITY_COMMON, [Effect::BLINDNESS], [20], [0], [100]));
        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::CRIPPLE, "Cripple", CustomEnchant::RARITY_COMMON, [Effect::NAUSEA, Effect::SLOWNESS], [100, 100], [0, 1]));
        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::POISON, "Poison", CustomEnchant::RARITY_UNCOMMON, [Effect::POISON]));
        self::registerEnchantment(new LacedWeaponEnchant($plugin, CustomEnchantIds::WITHER, "Wither", CustomEnchant::RARITY_UNCOMMON, [Effect::WITHER]));

        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::BLAZE, "Blaze", "PiggyFireball"));
        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::HOMING, "Homing", "HomingArrow", 3, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::PORKIFIED, "Porkified", "PigProjectile", 3, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new ProjectileChangingEnchant($plugin, CustomEnchantIds::WITHERSKULL, "Wither Skull", "PiggyWitherSkull", 1, CustomEnchant::RARITY_MYTHIC));

        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::ENRAGED, "Enraged", 5, CustomEnchant::TYPE_CHESTPLATE, CustomEnchant::ITEM_TYPE_CHESTPLATE, Effect::STRENGTH, -1));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::GEARS, "Gears", 1, CustomEnchant::TYPE_BOOTS, CustomEnchant::ITEM_TYPE_BOOTS, Effect::SPEED, 0, 0, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::GLOWING, "Glowing", 1, CustomEnchant::TYPE_HELMET, CustomEnchant::ITEM_TYPE_HELMET, Effect::NIGHT_VISION, 0, 0, CustomEnchant::RARITY_COMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::HASTE, "Haste", 5, CustomEnchant::TYPE_HAND, CustomEnchant::ITEM_TYPE_PICKAXE, Effect::HASTE, 0, 1, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::OBSIDIANSHIELD, "Obsidian Shield", 1, CustomEnchant::TYPE_ARMOR_INVENTORY, CustomEnchant::ITEM_TYPE_ARMOR, Effect::FIRE_RESISTANCE, 0, 0, CustomEnchant::RARITY_COMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::OXYGENATE, "Oxygenate", 1, CustomEnchant::TYPE_HAND, CustomEnchant::ITEM_TYPE_PICKAXE, Effect::WATER_BREATHING, 0, 0, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ToggleableEffectEnchant($plugin, CustomEnchantIds::SPRINGS, "Springs", 1, CustomEnchant::TYPE_BOOTS, CustomEnchant::ITEM_TYPE_BOOTS, Effect::JUMP_BOOST, 3, 0, CustomEnchant::RARITY_UNCOMMON));

        self::registerEnchantment(new AntiKnockbackEnchant($plugin, CustomEnchantIds::ANTIKNOCKBACK));
        self::registerEnchantment(new AntitoxinEnchant($plugin, CustomEnchantIds::ANTITOXIN, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new AutoAimEnchant($plugin, CustomEnchantIds::AUTOAIM, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new AutoRepairEnchant($plugin, CustomEnchantIds::AUTOREPAIR, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ArmoredEnchant($plugin, CustomEnchantIds::ARMORED));
        self::registerEnchantment(new BerserkerEnchant($plugin, CustomEnchantIds::BERSERKER));
        self::registerEnchantment(new BlessedEnchant($plugin, CustomEnchantIds::BLESSED, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new BombardmentEnchant($plugin, CustomEnchantIds::BOMBARDMENT));
        self::registerEnchantment(new BountyHunterEnchant($plugin, CustomEnchantIds::BOUNTYHUNTER, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new CactusEnchant($plugin, CustomEnchantIds::CACTUS));
        self::registerEnchantment(new ChickenEnchant($plugin, CustomEnchantIds::CHICKEN, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new CloakingEnchant($plugin, CustomEnchantIds::CLOAKING, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new DeathbringerEnchant($plugin, CustomEnchantIds::DEATHBRINGER));
        self::registerEnchantment(new DeepWoundsEnchant($plugin, CustomEnchantIds::DEEPWOUNDS));
        self::registerEnchantment(new DisarmingEnchant($plugin, CustomEnchantIds::DISARMING, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new DisarmorEnchant($plugin, CustomEnchantIds::DISARMOR, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new DrillerEnchant($plugin, CustomEnchantIds::DRILLER, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new EndershiftEnchant($plugin, CustomEnchantIds::ENDERSHIFT));
        self::registerEnchantment(new EnergizingEnchant($plugin, CustomEnchantIds::ENERGIZING));
        self::registerEnchantment(new EnlightedEnchant($plugin, CustomEnchantIds::ENLIGHTED, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ExplosiveEnchant($plugin, CustomEnchantIds::EXPLOSIVE, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new FarmerEnchant($plugin, CustomEnchantIds::FARMER, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new FertilizerEnchant($plugin, CustomEnchantIds::FERTILIZER, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new FocusedEnchant($plugin, CustomEnchantIds::FOCUSED, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ForcefieldEnchant($plugin, CustomEnchantIds::FORCEFIELD, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new GooeyEnchant($plugin, CustomEnchantIds::GOOEY));
        self::registerEnchantment(new GrapplingEnchant($plugin, CustomEnchantIds::GRAPPLING));
        self::registerEnchantment(new GrowEnchant($plugin, CustomEnchantIds::GROW, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new HallucinationEnchant($plugin, CustomEnchantIds::HALLUCINATION, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new HarvestEnchant($plugin, CustomEnchantIds::HARVEST, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new HeadhunterEnchant($plugin, CustomEnchantIds::HEADHUNTER, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new HealingEnchant($plugin, CustomEnchantIds::HEALING));
        self::registerEnchantment(new HeavyEnchant($plugin, CustomEnchantIds::HEAVY));
        self::registerEnchantment(new ImplantsEnchant($plugin, CustomEnchantIds::IMPLANTS));
        self::registerEnchantment(new JackpotEnchant($plugin, CustomEnchantIds::JACKPOT, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new JetpackEnchant($plugin, CustomEnchantIds::JETPACK));
        self::registerEnchantment(new LifestealEnchant($plugin, CustomEnchantIds::LIFESTEAL, CustomEnchant::RARITY_COMMON));
        self::registerEnchantment(new LightningEnchant($plugin, CustomEnchantIds::LIGHTNING, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new LumberjackEnchant($plugin, CustomEnchantIds::LUMBERJACK));
        self::registerEnchantment(new MagmaWalkerEnchant($plugin, CustomEnchantIds::MAGMAWALKER, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new MeditationEnchant($plugin, CustomEnchantIds::MEDITATION, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new MissileEnchant($plugin, CustomEnchantIds::MISSILE));
        self::registerEnchantment(new MolotovEnchant($plugin, CustomEnchantIds::MOLOTOV, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new MoltenEnchant($plugin, CustomEnchantIds::MOLTEN));
        self::registerEnchantment(new OverloadEnchant($plugin, CustomEnchantIds::OVERLOAD, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new ParachuteEnchant($plugin, CustomEnchantIds::PARACHUTE, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ParalyzeEnchant($plugin, CustomEnchantIds::PARALYZE));
        self::registerEnchantment(new PiercingEnchant($plugin, CustomEnchantIds::PIERCING));
        self::registerEnchantment(new PoisonousCloudEnchant($plugin, CustomEnchantIds::POISONOUSCLOUD));
        self::registerEnchantment(new ProwlEnchant($plugin, CustomEnchantIds::PROWL));
        self::registerEnchantment(new QuickeningEnchant($plugin, CustomEnchantIds::QUICKENING, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new RadarEnchant($plugin, CustomEnchantIds::RADAR));
        self::registerEnchantment(new ReviveEnchant($plugin, CustomEnchantIds::REVIVE));
        self::registerEnchantment(new SelfDestructEnchant($plugin, CustomEnchantIds::SELFDESTRUCT));
        self::registerEnchantment(new ShieldedEnchant($plugin, CustomEnchantIds::SHIELDED));
        self::registerEnchantment(new ShrinkEnchant($plugin, CustomEnchantIds::SHRINK, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new ShuffleEnchant($plugin, CustomEnchantIds::SHUFFLE));
        self::registerEnchantment(new SmeltingEnchant($plugin, CustomEnchantIds::SMELTING, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new SoulboundEnchant($plugin, CustomEnchantIds::SOULBOUND, CustomEnchant::RARITY_MYTHIC));
        self::registerEnchantment(new SpiderEnchant($plugin, CustomEnchantIds::SPIDER));
        self::registerEnchantment(new StompEnchantment($plugin, CustomEnchantIds::STOMP, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new TankEnchant($plugin, CustomEnchantIds::TANK, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new TelepathyEnchant($plugin, CustomEnchantIds::TELEPATHY));
        self::registerEnchantment(new VacuumEnchant($plugin, CustomEnchantIds::VACUUM));
        self::registerEnchantment(new VampireEnchant($plugin, CustomEnchantIds::VAMPIRE, CustomEnchant::RARITY_UNCOMMON));
        self::registerEnchantment(new VolleyEnchant($plugin, CustomEnchantIds::VOLLEY, CustomEnchant::RARITY_UNCOMMON));
    }

    /**
     * @return PiggyCustomEnchants
     */
    public static function getPlugin(): PiggyCustomEnchants
    {
        return self::$plugin;
    }

    /**
     * @param CustomEnchant $enchant
     */
    public static function registerEnchantment(CustomEnchant $enchant): void
    {
        self::$enchants[$enchant->getId()] = $enchant;
        Enchantment::registerEnchantment($enchant);

        self::$plugin->getLogger()->debug("Custom Enchantment '" . $enchant->getName() . "' registered with id " . $enchant->getId());
    }

    /**
     * @param $id
     * @throws ReflectionException
     */
    public static function unregisterEnchantment($id): void
    {
        $id = $id instanceof Enchantment ? $id->getId() : $id;
        self::$enchants[$id]->unregister();
        self::$plugin->getLogger()->debug("Custom Enchantment '" . self::$enchants[$id]->getName() . "' unregistered with id " . self::$enchants[$id]->getId());
        unset(self::$enchants[$id]);

        $property = new ReflectionProperty(Enchantment::class, "enchantments");
        $property->setAccessible(true);
        $value = $property->getValue();
        unset($value[$id]);
        $property->setValue($value);
    }

    /**
     * @return CustomEnchant[]
     */
    public static function getEnchantments(): array
    {
        return self::$enchants;
    }

    /**
     * @param int $id
     * @return CustomEnchant|null
     */
    public static function getEnchantment(int $id): ?CustomEnchant
    {
        return self::$enchants[$id] ?? null;
    }

    /**
     * @param string $name
     * @return CustomEnchant|null
     */
    public static function getEnchantmentByName(string $name): ?CustomEnchant
    {
        foreach (self::$enchants as $enchant) {
            if (strtolower(str_replace(" ", "", $enchant->getName())) === strtolower(str_replace(" ", "", $name))) return $enchant;
        }
        return null;
    }
}
