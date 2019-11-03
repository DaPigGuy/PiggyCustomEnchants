<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\blocks\PiggyObsidian;
use DaPigGuy\PiggyCustomEnchants\commands\CustomEnchantsCommand;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyFireball;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyLightning;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyWitherSkull;
use DaPigGuy\PiggyCustomEnchants\entities\PigProjectile;
use DaPigGuy\PiggyCustomEnchants\tasks\CheckDisabledEnchantsTask;
use DaPigGuy\PiggyCustomEnchants\tasks\CheckUpdatesTask;
use DaPigGuy\PiggyCustomEnchants\tasks\TickEnchantmentsTask;
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
    /** @var Config */
    public $descriptions;

    /**
     * @throws ReflectionException
     */
    public function onEnable(): void
    {
        $this->saveResource("descriptions.json");
        $this->descriptions = new Config($this->getDataFolder() . "descriptions.json");
        $this->saveDefaultConfig();

        CustomEnchantManager::init($this);

        BlockFactory::registerBlock(new PiggyObsidian(), true);

        Entity::registerEntity(PigProjectile::class, true);
        Entity::registerEntity(PiggyFireball::class, true);
        Entity::registerEntity(PiggyWitherSkull::class, true);

        Entity::registerEntity(PiggyLightning::class, true);
        Entity::registerEntity(PiggyTNT::class, true);

        foreach ($this->getConfig()->get("disabled-enchants") as $enchant) {
            $e = CustomEnchantManager::getEnchantmentByName($enchant);
            if ($e instanceof CustomEnchant) CustomEnchantManager::unregisterEnchantment($e->getId());
        }

        $this->getServer()->getCommandMap()->register("PiggyCustomEnchants", new CustomEnchantsCommand($this, "PiggyCustomEnchants", "Manage Custom Enchants", ["ce", "customenchant"]));

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
                    $enchantment = $enchantmentInstance->getType();
                    if ($enchantment instanceof CustomEnchant && $enchantment->canToggle()) {
                        $enchantment->onToggle($player, $content, $player->getArmorInventory(), $slot, $enchantmentInstance->getLevel(), false);
                    }
                }
            }
        }
    }

    /**
     * @param CustomEnchant $enchant
     * @return string
     */
    public function getEnchantmentDescription(CustomEnchant $enchant): string
    {
        return (string)$this->descriptions->get(strtolower(str_replace(" ", "", $enchant->getName())));
    }

    /**
     * @return bool
     */
    public function areFormsEnabled(): bool
    {
        return $this->getConfig()->getNested("forms.enabled", true);
    }
}