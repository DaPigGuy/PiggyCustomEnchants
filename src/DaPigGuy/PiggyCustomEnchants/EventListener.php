<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\DrillerEnchant;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\object\FallingBlock;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
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
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;

class EventListener implements Listener
{
    /** @var PiggyCustomEnchants */
    private $plugin;

    public function __construct(PiggyCustomEnchants $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        ReactiveEnchantment::attemptReaction($player, $event);
    }

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
            if ($packet->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM) {
                if ($packet->trData->actionType === InventoryTransactionPacket::USE_ITEM_ACTION_BREAK_BLOCK) {
                    DrillerEnchant::$lastBreakFace[$event->getPlayer()->getName()] = $packet->trData->face;
                }
            }
        }
        if ($packet instanceof MobEquipmentPacket) {
            Utils::filterDisplayedEnchants($packet->item);
        }
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof InventorySlotPacket) {
            Utils::displayEnchants($packet->item->getItemStack());
        }
        if ($packet instanceof InventoryContentPacket) {
            foreach ($packet->items as $item) {
                Utils::displayEnchants($item->getItemStack());
            }
        }
    }

    /**
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
            foreach ($oldItem->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($entity, $oldItem, $enchantmentInstance, $inventory, $slot, false);
            foreach ($newItem->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($entity, $newItem, $enchantmentInstance, $inventory, $slot);
        }
    }

    public function onBlockChange(EntityBlockChangeEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof FallingBlock && ($bombardmentLevel = $entity->namedtag->getInt("Bombardment", 0)) > 0) {
            for ($i = 0; $i < 3 + $bombardmentLevel; $i++) {
                $nbt = Entity::createBaseNBT($entity);
                $nbt->setShort("Fuse", 0);

                /** @var PiggyTNT $tnt */
                $tnt = Entity::createEntity("PiggyTNT", $entity->getLevel(), $nbt);
                $tnt->worldDamage = $this->plugin->getConfig()->getNested("world-damage.bombardment", false);
                $tnt->setOwningEntity($entity->getOwningEntity());
                $tnt->spawnToAll();
            }
            $event->setCancelled();
        }
    }

    /**
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
            ReactiveEnchantment::attemptReaction($entity, $event);
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            $attacker = $event->getDamager();
            if ($attacker instanceof Player) {
                ReactiveEnchantment::attemptReaction($attacker, $event);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onEffectAdd(EntityEffectAddEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            ReactiveEnchantment::attemptReaction($entity, $event);
        }
    }

    /**
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
            if ($oldItem->equals($newItem, false, true)) return;
            foreach ($oldItem->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($entity, $oldItem, $enchantmentInstance, $inventory, $slot, false);
            foreach ($newItem->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($entity, $newItem, $enchantmentInstance, $inventory, $slot);
        }
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            ReactiveEnchantment::attemptReaction($entity, $event);
        }
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        ReactiveEnchantment::attemptReaction($player, $event);
    }

    public function onIllegalMove(PlayerIllegalMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantIds::SPIDER) !== null) {
            $event->setCancelled();
        }
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        ReactiveEnchantment::attemptReaction($player, $event);
        if ($this->plugin->getConfig()->getNested("miscellaneous.armor-hold-equip", false) && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR) {
            if ($item instanceof Armor || $item->getId() === Item::ELYTRA || $item->getId() === Item::PUMPKIN || $item->getId() === Item::SKULL) {
                $slot = 0;
                if (Utils::isChestplate($item)) $slot = 1;
                if (Utils::isLeggings($item)) $slot = 2;
                if (Utils::isBoots($item)) $slot = 3;
                $player->getInventory()->setItemInHand($player->getArmorInventory()->getItem($slot));
                $player->getArmorInventory()->setItem($slot, $item);
                $event->setCancelled();
            }
        }
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onItemHold(PlayerItemHeldEvent $event): void
    {
        $player = $event->getPlayer();
        $inventory = $player->getInventory();
        $oldItem = $inventory->getItemInHand();
        $newItem = $event->getItem();
        foreach ($oldItem->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $oldItem, $enchantmentInstance, $inventory, $inventory->getHeldItemIndex(), false);
        foreach ($newItem->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $newItem, $enchantmentInstance, $inventory, $inventory->getHeldItemIndex());
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot);
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot);
        }
    }

    public function onKick(PlayerKickEvent $event): void
    {
        $player = $event->getPlayer();
        if ($event->getReason() === "Flying is not enabled on this server") {
            if ($player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantIds::SPIDER) !== null) {
                $event->setCancelled();
            }
        }
    }

    /**
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
        ReactiveEnchantment::attemptReaction($player, $event);
    }

    /**
     * @priority MONITOR
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player->isClosed()) {
            foreach ($player->getInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot, false);
            }
            foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
                foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot, false);
            }
        }
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onSneak(PlayerToggleSneakEvent $event): void
    {
        $player = $event->getPlayer();
        ReactiveEnchantment::attemptReaction($player, $event);
    }

    /**
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void
    {
        $projectile = $event->getEntity();
        $shooter = $projectile->getOwningEntity();
        if ($shooter instanceof Player) {
            ReactiveEnchantment::attemptReaction($shooter, $event);
        }
    }

    /**
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
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onTransaction(InventoryTransactionEvent $event): void
    {
        if (!$this->plugin->getConfig()->getNested("enchants.books", true)) return;
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getId() === ItemIds::ENCHANTED_BOOK && ($itemClicked = $action->getSourceItem())->getId() !== ItemIds::AIR) {
                    if (count($itemClickedWith->getEnchantments()) < 1) return;
                    $enchantmentSuccessful = false;
                    foreach ($itemClickedWith->getEnchantments() as $enchantment) {
                        $newLevel = $enchantment->getLevel();
                        if (($existingEnchant = $itemClicked->getEnchantment($enchantment->getId())) !== null) {
                            if ($existingEnchant->getLevel() > $newLevel) continue;
                            $newLevel = $existingEnchant->getLevel() === $newLevel ? $newLevel + 1 : $newLevel;
                        }
                        if (!Utils::canBeEnchanted($itemClicked, $enchantment->getType(), $newLevel) || ($itemClicked->getId() === ItemIds::ENCHANTED_BOOK && count($itemClicked->getEnchantments()) === 0)) continue;
                        $itemClicked->addEnchantment($enchantment->setLevel($newLevel));
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $enchantmentSuccessful = true;
                    }
                    if ($enchantmentSuccessful) {
                        $event->setCancelled();
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), Item::get(Item::AIR));
                    }
                }
            }
        }
    }
}