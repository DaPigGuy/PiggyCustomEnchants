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
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use ReflectionException;

/**
 * Class PiggyCustomEnchants
 * @package DaPigGuy\PiggyCustomEnchants
 */
class PiggyCustomEnchants extends PluginBase
{
    /** @var array */
    private $enchantmentDisplayNames;
    /** @var array */
    private $enchantmentDescriptions;

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

        $this->saveResource("descriptions.json");
        $this->enchantmentDescriptions = (new Config($this->getDataFolder() . "descriptions.json"))->getAll();
        $this->saveResource("display_names.json");
        $this->enchantmentDisplayNames = (new Config($this->getDataFolder() . "display_names.json"))->getAll();
        $this->saveDefaultConfig();

        CustomEnchantManager::init($this);

        BlockFactory::registerBlock(new PiggyObsidian(), true);

        foreach ([HomingArrow::class, PigProjectile::class, PiggyFireball::class, PiggyWitherSkull::class, PiggyLightning::class, PiggyTNT::class] as $entityClassName) {
            Entity::registerEntity($entityClassName, true);
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
                foreach ($content->getEnchantments() as $enchantmentInstance) {
                    /** @var ToggleableEnchantment $enchantment */
                    $enchantment = $enchantmentInstance->getType();
                    if ($enchantment instanceof CustomEnchant && $enchantment->canToggle()) {
                        $enchantment->onToggle($player, $content, $player->getInventory(), $slot, $enchantmentInstance->getLevel(), false);
                    }
                }
            }
            foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) {
                    /** @var ToggleableEnchantment $enchantment */
                    $enchantment = $enchantmentInstance->getType();
                    if ($enchantment instanceof CustomEnchant && $enchantment->canToggle()) {
                        $enchantment->onToggle($player, $content, $player->getArmorInventory(), $slot, $enchantmentInstance->getLevel(), false);
                    }
                }
            }
        }
    }

    /**
     * @return array
     * @internal
     */
    public function getEnchantmentDisplayNames(): array
    {
        return $this->enchantmentDisplayNames;
    }

    /**
     * @return array
     * @internal
     */
    public function getEnchantmentDescriptions(): array
    {
        return $this->enchantmentDescriptions;
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