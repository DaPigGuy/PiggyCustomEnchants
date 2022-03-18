<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;
use DaPigGuy\libPiggyUpdateChecker\libPiggyUpdateChecker;
use DaPigGuy\PiggyCustomEnchants\blocks\PiggyObsidian;
use DaPigGuy\PiggyCustomEnchants\commands\CustomEnchantsCommand;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\BombardmentTNT;
use DaPigGuy\PiggyCustomEnchants\entities\HomingArrow;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyFireball;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyLightning;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyWitherSkull;
use DaPigGuy\PiggyCustomEnchants\entities\PigProjectile;
use DaPigGuy\PiggyCustomEnchants\tasks\CheckDisabledEnchantsTask;
use DaPigGuy\PiggyCustomEnchants\tasks\TickEnchantmentsTask;
use jojoe77777\FormAPI\Form;
use pocketmine\block\BlockFactory;
use pocketmine\color\Color;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\World;

class PiggyCustomEnchants extends PluginBase
{
    public static Effect $SLOW_FALL;

    /** @var mixed[] */
    private array $enchantmentData;

    public function onEnable(): void
    {
        foreach (
            [
                "Commando" => BaseCommand::class,
                "libformapi" => Form::class,
                "libPiggyUpdateChecker" => libPiggyUpdateChecker::class
            ] as $virion => $class
        ) {
            if (!class_exists($class)) {
                $this->getLogger()->error($virion . " virion not found. Download PiggyCustomEnchants at https://poggit.pmmp.io/p/PiggyCustomEnchants for a pre-compiled phar.");
                $this->getServer()->getPluginManager()->disablePlugin($this);
                return;
            }
        }

        foreach (["rarities", "max_levels", "display_names", "descriptions", "extra_data", "cooldowns", "chances"] as $file) {
            $this->saveResource($file . ".json");
            foreach ((new Config($this->getDataFolder() . $file . ".json"))->getAll() as $enchant => $data) {
                $this->enchantmentData[$enchant][$file] = $data;
            }
        }
        $this->saveDefaultConfig();

        CustomEnchantManager::init($this);

        BlockFactory::getInstance()->register(new PiggyObsidian(), true);

        //TODO: Use real effect
        self::$SLOW_FALL = new Effect("%potion.slowFalling", new Color(206, 255, 255));
        EffectIdMap::getInstance()->register(27, self::$SLOW_FALL);

        $entityFactory = EntityFactory::getInstance();
        $entityFactory->register(BombardmentTNT::class, function (World $world, CompoundTag $nbt): BombardmentTNT {
            return new BombardmentTNT(EntityDataHelper::parseLocation($nbt, $world), $nbt, $nbt->getInt("Level", 1));
        }, ["BombardmentTNT"]);
        $entityFactory->register(HomingArrow::class, function (World $world, CompoundTag $nbt): HomingArrow {
            return new HomingArrow(EntityDataHelper::parseLocation($nbt, $world), null, false, $nbt, $nbt->getInt("Level", 1));
        }, ["HomingArrow"]);
        $entityFactory->register(PigProjectile::class, function (World $world, CompoundTag $nbt): PigProjectile {
            return new PigProjectile(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ["PigProjectile"]);
        $entityFactory->register(PiggyFireball::class, function (World $world, CompoundTag $nbt): PiggyFireball {
            return new PiggyFireball(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ["PiggyFireball"]);
        $entityFactory->register(PiggyLightning::class, function (World $world, CompoundTag $nbt): PiggyLightning {
            return new PiggyLightning(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["PiggyLightning"]);
        $entityFactory->register(PiggyTNT::class, function (World $world, CompoundTag $nbt): PiggyTNT {
            return new PiggyTNT(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["PiggyTNT"]);
        $entityFactory->register(PiggyWitherSkull::class, function (World $world, CompoundTag $nbt): PiggyWitherSkull {
            return new PiggyWitherSkull(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ["PiggyWitherSkull"]);

        foreach ($this->getConfig()->get("disabled-enchants", []) as $enchant) {
            $e = CustomEnchantManager::getEnchantmentByName($enchant);
            if ($e instanceof CustomEnchant) CustomEnchantManager::unregisterEnchantment($e->getId());
        }

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register("piggycustomenchants", new CustomEnchantsCommand($this, "customenchants", "Manage Custom Enchants", ["ce", "customenchant"]));

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new TickEnchantmentsTask($this), 1);

        libPiggyUpdateChecker::init($this);
        if ($this->getConfig()->get("remote-disable", true) === true) $this->getServer()->getAsyncPool()->submitTask(new CheckDisabledEnchantsTask());
    }

    public function onDisable(): void
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            foreach ($player->getInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) {
                    ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot, false);
                }
            }
            foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) {
                    ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot, false);
                }
            }
        }
    }

    /**
     * @internal
     */
    public function getEnchantmentData(string $enchant, string $data, int|string|array $default = ""): mixed
    {
        if (!isset($this->enchantmentData[str_replace(" ", "", strtolower($enchant))][$data])) $this->setEnchantmentData($enchant, $data, $default);
        return $this->enchantmentData[str_replace(" ", "", strtolower($enchant))][$data];
    }

    public function setEnchantmentData(string $enchant, string $data, int|string|array $value): void
    {
        $this->enchantmentData[str_replace(" ", "", strtolower($enchant))][$data] = $value;
        $config = new Config($this->getDataFolder() . $data . ".json");
        $config->set(str_replace(" ", "", strtolower($enchant)), $value);
        $config->save();
    }

    /**
     * @internal
     */
    public function areFormsEnabled(): bool
    {
        return $this->getConfig()->getNested("forms.enabled", true);
    }
}