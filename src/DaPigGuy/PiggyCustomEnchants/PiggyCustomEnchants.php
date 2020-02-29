<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
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
use DaPigGuy\PiggyCustomEnchants\tasks\CheckUpdatesTask;
use DaPigGuy\PiggyCustomEnchants\tasks\TickEnchantmentsTask;
use jojoe77777\FormAPI\Form;
use pocketmine\block\BlockFactory;
use pocketmine\entity\EntityFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ReflectionException;

/**
 * Class PiggyCustomEnchants
 * @package DaPigGuy\PiggyCustomEnchants
 */
class PiggyCustomEnchants extends PluginBase
{
    /** @var array[] */
    private $enchantmentData;

    /**
     * @throws ReflectionException
     * @throws HookAlreadyRegistered
     */
    public function onEnable(): void
    {
        if (!class_exists(BaseCommand::class)) {
            $this->getLogger()->error("Commando virion not found. Please download PiggyCustomEnchants from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        if (!class_exists(Form::class)) {
            $this->getLogger()->error("libformapi virion not found. Please download PiggyCustomEnchants from Poggit-CI or use DEVirion (not recommended).");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        foreach (["max_levels", "display_names", "descriptions", "extra_data"] as $file) {
            $this->saveResource($file . ".json");
            foreach ((new Config($this->getDataFolder() . $file . ".json"))->getAll() as $enchant => $data) {
                $this->enchantmentData[$enchant][$file] = $data;
            }
        }
        $this->saveDefaultConfig();

        CustomEnchantManager::init($this);

        BlockFactory::register(new PiggyObsidian(), true);

        foreach ([BombardmentTNT::class, HomingArrow::class, PigProjectile::class, PiggyFireball::class, PiggyWitherSkull::class, PiggyLightning::class, PiggyTNT::class] as $entityClassName) {
            EntityFactory::register($entityClassName, []);
        }

        foreach ($this->getConfig()->get("disabled-enchants") as $enchant) {
            $e = CustomEnchantManager::getEnchantmentByName($enchant);
            if ($e instanceof CustomEnchant) CustomEnchantManager::unregisterEnchantment($e->getId());
        }

        if (!PacketHooker::isRegistered()) PacketHooker::register($this);
        $this->getServer()->getCommandMap()->register("piggycustomenchants", new CustomEnchantsCommand($this, "customenchants", "Manage Custom Enchants", ["ce", "customenchant"]));

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask(new TickEnchantmentsTask($this), 1);

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdatesTask($this->getDescription()->getVersion(), $this->getDescription()->getCompatibleApis()[0]));
        if ($this->getConfig()->get("remote-disable", true)) $this->getServer()->getAsyncPool()->submitTask(new CheckDisabledEnchantsTask());
    }

    public function onDisable(): void
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            foreach ($player->getInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot, false);
            }
            foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot, false);
            }
        }
    }

    /**
     * @param string $enchant
     * @param string $data
     * @param int|string|array $default
     * @return mixed
     * @internal
     */
    public function getEnchantmentData(string $enchant, string $data, $default = "")
    {
        return $this->enchantmentData[str_replace(" ", "", strtolower($enchant))][$data] ?? $default;
    }

    /**
     * @return bool
     * @internal
     */
    public function areFormsEnabled(): bool
    {
        return $this->getConfig()->getNested("forms.enabled", true);
    }
}