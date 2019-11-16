<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\DrillerEnchant;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;

/**
 * Class EventListener
 * @package DaPigGuy\PiggyCustomEnchants
 */
class EventListener implements Listener
{
    /** @var PiggyCustomEnchants */
    private $plugin;

    /**
     * EventListener constructor.
     * @param PiggyCustomEnchants $plugin
     */
    public function __construct(PiggyCustomEnchants $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param BlockBreakEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $this->attemptReaction($player, $event);
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof InventoryTransactionPacket) {
            foreach ($packet->actions as $key => $action) {
                Utils::filterDisplayedEnchants($action->oldItem);
                Utils::filterDisplayedEnchants($action->newItem);
                $packet->actions[$key] = $action;
            }
            if (isset($packet->trData->itemInHand)) {
                Utils::filterDisplayedEnchants($packet->trData->itemInHand);
            }
        }
        if ($packet instanceof MobEquipmentPacket) {
            Utils::filterDisplayedEnchants($packet->item);
        }
        if ($packet instanceof PlayerActionPacket) {
            if ($packet->action === PlayerActionPacket::ACTION_CONTINUE_BREAK) {
                DrillerEnchant::$lastBreakFace[$event->getPlayer()->getName()] = $packet->face;
            }
        }
    }

    /**
     * @param DataPacketSendEvent $event
     */
    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof InventorySlotPacket) {
            Utils::displayEnchants($packet->item);
        }
        if ($packet instanceof InventoryContentPacket) {
            foreach ($packet->items as $key => $item) {
                $packet->items[$key] = Utils::displayEnchants($item);
            }
        }
    }

    /**
     * @param EntityArmorChangeEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onArmorChange(EntityArmorChangeEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $oldItem = $event->getOldItem();
            $newItem = $event->getNewItem();
            $inventory = $entity->getArmorInventory();
            $slot = $event->getSlot();
            if ($oldItem->equals($newItem, false, true)) return;
            foreach ($oldItem->getEnchantments() as $enchantmentInstance) {
                /** @var ToggleableEnchantment $enchantment */
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && in_array($enchantment->getUsageType(), [CustomEnchant::TYPE_ARMOR_INVENTORY, CustomEnchant::TYPE_HELMET, CustomEnchant::TYPE_CHESTPLATE, CustomEnchant::TYPE_LEGGINGS, CustomEnchant::TYPE_BOOTS])) {
                    $enchantment->onToggle($entity, $oldItem, $inventory, $slot, $enchantmentInstance->getLevel(), false);
                }
            }
            foreach ($newItem->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() &&
                    (
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_HELMET && Utils::isHelmet($newItem) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_CHESTPLATE && Utils::isChestplate($newItem) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_LEGGINGS && Utils::isLeggings($newItem) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_BOOTS && Utils::isBoots($newItem)
                    )
                ) {
                    $enchantment->onToggle($entity, $newItem, $inventory, $slot, $enchantmentInstance->getLevel(), true);
                }
            }
        }
    }

    /**
     * @param EntityBlockChangeEvent $event
     */
    public function onBlockChange(EntityBlockChangeEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof FallingBlock && ($bombardmentLevel = $entity->namedtag->getInt("Bombardment", 0)) > 0) {
            for ($i = 0; $i < 3 + $bombardmentLevel; $i++) {
                $nbt = Entity::createBaseNBT($entity);
                $nbt->setShort("Fuse", 0);

                $tnt = Entity::createEntity("PiggyTNT", $entity->getLevel(), $nbt);
                $tnt->worldDamage = $this->plugin->getConfig()->getNested("world-damage.bombardment", false);
                $tnt->setOwningEntity($entity->getOwningEntity());
                $tnt->spawnToAll();
            }
            $event->setCancelled();
        }
    }

    /**
     * @param EntityDamageEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL && !Utils::shouldTakeFallDamage($entity)) {
                if ($entity->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantIds::SPRINGS) === null) Utils::setShouldTakeFallDamage($entity, true);
                $event->setCancelled();
                return;
            }
            $this->attemptReaction($entity, $event);
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            $attacker = $event->getDamager();
            if ($attacker instanceof Player) {
                $this->attemptReaction($attacker, $event);
            }
        }
    }

    /**
     * @param EntityEffectAddEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onEffectAdd(EntityEffectAddEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $this->attemptReaction($entity, $event);
        }
    }

    /**
     * @param EntityInventoryChangeEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onInventoryChange(EntityInventoryChangeEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $oldItem = $event->getOldItem();
            $newItem = $event->getNewItem();
            $inventory = $entity->getInventory();
            $slot = $event->getSlot();
            if ($newItem->getId() === Item::AIR) {
                foreach ($oldItem->getEnchantments() as $enchantmentInstance) {
                    /** @var ToggleableEnchantment $enchantment */
                    $enchantment = $enchantmentInstance->getType();
                    if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && (($enchantment->getUsageType() === CustomEnchant::TYPE_HAND && $slot === $inventory->getHeldItemIndex()) || $enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY)) {
                        $enchantment->onToggle($entity, $oldItem, $inventory, $slot, $enchantmentInstance->getLevel(), false);
                    }
                }
            }
            if ($oldItem->getId() === Item::AIR) {
                foreach ($newItem->getEnchantments() as $enchantmentInstance) {
                    $enchantment = $enchantmentInstance->getType();
                    if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && ($enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY)) {
                        $enchantment->onToggle($entity, $newItem, $inventory, $slot, $enchantmentInstance->getLevel(), true);
                    }
                }
            }
        }
    }

    /**
     * @param EntityShootBowEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $this->attemptReaction($entity, $event);
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $this->attemptReaction($player, $event);
    }

    /**
     * @param PlayerIllegalMoveEvent $event
     */
    public function onIllegalMove(PlayerIllegalMoveEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantIds::SPIDER) !== null) {
            $event->setCancelled();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $this->attemptReaction($player, $event);
        if ($this->plugin->getConfig()->getNested("miscellaneous.armor-hold-equip", false) && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR) {
            if ($item instanceof Armor || $item->getId() === Item::PUMPKIN || $item->getId() === Item::SKULL) {
                $slot = 0;
                if (Utils::isChestplate($item)) $slot = 1;
                if (Utils::isLeggings($item)) $slot = 2;
                if (Utils::isBoots($item)) $slot = 3;
                if ($player->getArmorInventory()->getItem($slot)->getId() === Item::AIR) {
                    $player->getArmorInventory()->setItem($slot, $item);
                    $player->getInventory()->setItemInHand(Item::get(Item::AIR));
                }
                $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerItemHeldEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onItemHold(PlayerItemHeldEvent $event): void
    {
        $player = $event->getPlayer();
        $inventory = $player->getInventory();
        $oldItem = $inventory->getItemInHand();
        $newItem = $event->getItem();
        foreach ($oldItem->getEnchantments() as $enchantmentInstance) {
            /** @var ToggleableEnchantment $enchantment */
            $enchantment = $enchantmentInstance->getType();
            if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && ($enchantment->getUsageType() === CustomEnchant::TYPE_HAND || $enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY)) {
                $enchantment->onToggle($player, $oldItem, $inventory, $inventory->getHeldItemIndex(), $enchantmentInstance->getLevel(), false);
            }
        }
        foreach ($newItem->getEnchantments() as $enchantmentInstance) {
            $enchantment = $enchantmentInstance->getType();
            if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && ($enchantment->getUsageType() === CustomEnchant::TYPE_HAND || $enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY)) {
                $enchantment->onToggle($player, $newItem, $inventory, $event->getSlot(), $enchantmentInstance->getLevel(), true);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getInventory()->getItemInHand()->getEnchantments() as $enchantmentInstance) {
            /** @var ToggleableEnchantment $enchantment */
            $enchantment = $enchantmentInstance->getType();
            if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && ($enchantment->getUsageType() === CustomEnchant::TYPE_HAND || $enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY)) {
                $enchantment->onToggle($player, $player->getInventory()->getItemInHand(), $player->getInventory(), $player->getInventory()->getHeldItemIndex(), $enchantmentInstance->getLevel(), true);
            }
        }
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && ($enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY || $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY)) {
                    $enchantment->onToggle($player, $content, $player->getInventory(), $slot, $enchantmentInstance->getLevel(), true);
                }
            }
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() && (
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_HELMET && Utils::isHelmet($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_CHESTPLATE && Utils::isChestplate($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_LEGGINGS && Utils::isLeggings($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_BOOTS && Utils::isBoots($content)
                    )) {
                    $enchantment->onToggle($player, $content, $player->getInventory(), $slot, $enchantmentInstance->getLevel(), true);
                }
            }
        }
    }

    /**
     * @param PlayerKickEvent $event
     */
    public function onKick(PlayerKickEvent $event)
    {
        $player = $event->getPlayer();
        if ($event->getReason() === "Flying is not enabled on this server") {
            if ($player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantIds::SPIDER) !== null) {
                $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if (!Utils::shouldTakeFallDamage($player)) {
            if ($player->getLevel()->getBlock($player->floor()->subtract(0, 1))->getId() !== Block::AIR && Utils::getNoFallDamageDuration($player) <= 0) {
                Utils::setShouldTakeFallDamage($player, true);
            } else {
                Utils::increaseNoFallDamageDuration($player);
            }
        }
        if ($event->getFrom()->floor()->equals($event->getTo()->floor())) {
            return;
        }
        $this->attemptReaction($player, $event);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) {
                /** @var ToggleableEnchantment $enchantment */
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() &&
                    (
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_INVENTORY ||
                        ($enchantment->getUsageType() === CustomEnchant::TYPE_HAND && $slot === $player->getInventory()->getHeldItemIndex())
                    )
                ) {
                    $enchantment->onToggle($player, $content, $player->getInventory(), $slot, $enchantmentInstance->getLevel(), false);
                }
            }
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) {
                $enchantment = $enchantmentInstance->getType();
                if ($enchantment instanceof CustomEnchant && $enchantment->canToggle() &&
                    (
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ANY_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_ARMOR_INVENTORY ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_HELMET && Utils::isHelmet($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_CHESTPLATE && Utils::isChestplate($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_LEGGINGS && Utils::isLeggings($content) ||
                        $enchantment->getUsageType() === CustomEnchant::TYPE_BOOTS && Utils::isBoots($content))
                ) {
                    $enchantment->onToggle($player, $content, $player->getArmorInventory(), $slot, $enchantmentInstance->getLevel(), false);
                }
            }
        }
    }

    /**
     * @param PlayerToggleSneakEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onSneak(PlayerToggleSneakEvent $event): void
    {
        $player = $event->getPlayer();
        $this->attemptReaction($player, $event);
    }

    /**
     * @param ProjectileHitBlockEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void
    {
        $projectile = $event->getEntity();
        $shooter = $projectile->getOwningEntity();
        if ($shooter instanceof Player) {
            $this->attemptReaction($shooter, $event);
        }
    }

    /**
     * @param ProjectileLaunchEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onProjectileLaunch(ProjectileLaunchEvent $event): void
    {
        $projectile = $event->getEntity();
        $shooter = $projectile->getOwningEntity();
        if ($shooter instanceof Player) {
            ProjectileTracker::addProjectile($projectile, $shooter->getInventory()->getItemInHand());
        }
    }

    /**
     * @param Player $player
     * @param Event $event
     */
    public function attemptReaction(Player $player, Event $event): void
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

    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onTransaction(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if ($itemClickedWith->getId() === Item::ENCHANTED_BOOK && $itemClicked->getId() !== Item::AIR) {
                if (count($itemClickedWith->getEnchantments()) < 1) return;
                $enchantmentSuccessful = false;
                foreach ($itemClickedWith->getEnchantments() as $enchantment) {
                    if (!Utils::canBeEnchanted($itemClicked, $enchantment->getType(), $enchantment->getLevel())) continue;
                    $itemClicked->addEnchantment($enchantment);
                    $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);
                    $enchantmentSuccessful = true;
                }
                if ($enchantmentSuccessful) {
                    $event->setCancelled();
                    $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                }
            }
        }
    }
}