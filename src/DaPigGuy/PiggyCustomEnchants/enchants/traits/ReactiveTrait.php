<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\traits;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

trait ReactiveTrait
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    /** @var float[] */
    public $chanceMultiplier;

    public function canReact(): bool
    {
        return true;
    }

    public function getReagent(): array
    {
        return [EntityDamageByEntityEvent::class];
    }

    public function onReaction(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        $perWorldDisabledEnchants = $this->plugin->getConfig()->get("per-world-disabled-enchants");
        if (isset($perWorldDisabledEnchants[$player->getLevel()->getFolderName()]) && in_array(strtolower($this->name), $perWorldDisabledEnchants[$player->getLevel()->getFolderName()])) return;
        if ($this->getCooldown($player) > 0) return;
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getEntity() === $player && $event->getDamager() !== $player && $this->shouldReactToDamage()) return;
            if ($event->getEntity() !== $player && $this->shouldReactToDamaged()) return;
        }
        if (mt_rand(0 * 100000, 100 * 100000) / 100000 <= $this->getChance($player, $level)) {
            $this->react($player, $item, $inventory, $slot, $event, $level, $stack);
            $this->setCooldown($player, $this->cooldownDuration);
        }
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
    }

    public function getChance(Player $player, int $level): float
    {
        $base = $this->getBaseChance($level);
        $multiplier = $this->getChanceMultiplier($player);
        return $base * $multiplier;
    }

    public function getBaseChance(int $level): float
    {
        return ($this->plugin->getConfig()->getNested("chances." . strtolower(str_replace(" ", "", $this->getName())), 100)) * $level;
    }

    public function getChanceMultiplier(Player $player): float
    {
        return $this->chanceMultiplier[$player->getName()] ?? 1;
    }

    public function setChanceMultiplier(Player $player, float $multiplier): void
    {
        $this->chanceMultiplier[$player->getName()] = $multiplier;
    }

    public function getCooldownDuration(): int
    {
        return $this->cooldownDuration;
    }

    public function shouldReactToDamage(): bool
    {
        return $this->getItemType() === CustomEnchant::ITEM_TYPE_WEAPON || $this->getItemType() === CustomEnchant::ITEM_TYPE_BOW;
    }

    public function shouldReactToDamaged(): bool
    {
        return $this->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY;
    }

    public static function attemptReaction(Player $player, Event $event): void
    {
        if ($player->getInventory() === null) return;
        if ($event instanceof EntityDamageByChildEntityEvent || $event instanceof ProjectileHitBlockEvent) {
            $projectile = $event instanceof EntityDamageByEntityEvent ? $event->getChild() : $event->getEntity();
            if ($projectile instanceof Projectile && ProjectileTracker::isTrackedProjectile($projectile)) {
                if (!$event instanceof EntityDamageByEntityEvent || $event->getDamager() === $player) {
                    foreach (Utils::sortEnchantmentsByPriority(ProjectileTracker::getEnchantments($projectile)) as $enchantmentInstance) {
                        /** @var ReactiveEnchantment $enchantment */
                        $enchantment = $enchantmentInstance->getType();
                        if ($enchantment instanceof CustomEnchant && $enchantment->canReact()) {
                            if ($enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_HAND) {
                                foreach ($enchantment->getReagent() as $reagent) {
                                    if ($event instanceof $reagent) {
                                        $item = ProjectileTracker::getItem($projectile);
                                        $slot = 0;
                                        foreach ($player->getInventory()->getContents() as $s => $content) {
                                            if ($content->equalsExact($item)) $slot = $s;
                                        }
                                        $enchantment->onReaction($player, $item, $player->getInventory(), $slot, $event, $enchantmentInstance->getLevel(), 1);
                                    }
                                }
                            }
                        }
                    }
                    ProjectileTracker::removeProjectile($projectile);
                    return;
                }
            }
        }
        $enchantmentStacks = [];
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach (Utils::sortEnchantmentsByPriority($content->getEnchantments()) as $enchantmentInstance) {
                /** @var ReactiveEnchantment $enchantment */
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canReact()) {
                    if ($enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY || ($enchantment->getUsageType() === CustomEnchant::TYPE_HAND && $player->getInventory()->getHeldItemIndex() === $slot)) {
                        foreach ($enchantment->getReagent() as $reagent) {
                            if ($event instanceof $reagent) {
                                $enchantmentStacks[$enchantment->getId()] = ($enchantmentStacks[$enchantment->getId()] ?? 0) + $enchantmentInstance->getLevel();
                                $enchantment->onReaction($player, $content, $player->getInventory(), $slot, $event, $enchantmentInstance->getLevel(), $enchantmentStacks[$enchantment->getId()]);
                            }
                        }
                    }
                }
            }
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach (Utils::sortEnchantmentsByPriority($content->getEnchantments()) as $enchantmentInstance) {
                /** @var ReactiveEnchantment $enchantment */
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canReact()) {
                    if ((
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_HELMET && Utils::isHelmet($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_CHESTPLATE && Utils::isChestplate($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_LEGGINGS && Utils::isLeggings($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_BOOTS && Utils::isBoots($content)
                    )) {
                        foreach ($enchantment->getReagent() as $reagent) {
                            if ($event instanceof $reagent) {
                                $enchantmentStacks[$enchantment->getId()] = ($enchantmentStacks[$enchantment->getId()] ?? 0) + $enchantmentInstance->getLevel();
                                $enchantment->onReaction($player, $content, $player->getArmorInventory(), $slot, $event, $enchantmentInstance->getLevel(), $enchantmentStacks[$enchantment->getId()]);
                            }
                        }
                    }
                }
            }
        }
    }
}