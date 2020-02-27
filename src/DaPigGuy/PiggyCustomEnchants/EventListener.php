<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\DrillerEnchant;
use DaPigGuy\PiggyCustomEnchants\entities\BombardmentTNT;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use DaPigGuy\PiggyCustomEnchants\inventory\CustomEnchantToggleListener;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\EntityFactory;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\player\Player;

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
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        ReactiveEnchantment::attemptReaction($player, $event);
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof InventoryTransactionPacket) {
            $transaction = $packet->trData;
            foreach ($transaction->getActions() as $key => $action) {
                Utils::filterDisplayedEnchants($action->oldItem);
                Utils::filterDisplayedEnchants($action->newItem);
            }
            if ($transaction instanceof UseItemTransactionData || $transaction instanceof UseItemOnEntityTransactionData || $transaction instanceof ReleaseItemTransactionData) {
                Utils::filterDisplayedEnchants($transaction->getItemInHand());
            }
            if ($transaction instanceof UseItemTransactionData) {
                if ($transaction->getActionType() === UseItemTransactionData::ACTION_BREAK_BLOCK) {
                    DrillerEnchant::$lastBreakFace[$event->getOrigin()->getPlayer()->getName()] = $transaction->getFace();
                }
            }
        }
        if ($packet instanceof MobEquipmentPacket) {
            Utils::filterDisplayedEnchants($packet->item);
        }
    }

    /**
     * @param DataPacketSendEvent $event
     */
    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        $packets = $event->getPackets();
        foreach ($packets as $packet) {
            if ($packet instanceof InventorySlotPacket) {
                Utils::displayEnchants($packet->item);
            }
            if ($packet instanceof InventoryContentPacket) {
                foreach ($packet->items as $key => $item) {
                    $packet->items[$key] = Utils::displayEnchants($item);
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
        if ($entity instanceof BombardmentTNT) {
            for ($i = 0; $i < 3 + $entity->getEnchantmentLevel(); $i++) {
                /** @var PiggyTNT $tnt */
                $tnt = EntityFactory::create(PiggyTNT::class, $entity->getWorld(), EntityFactory::createBaseNBT($entity->getPosition())->setShort("Fuse", 0));
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
     */
    public function onDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL && !Utils::shouldTakeFallDamage($entity)) {
                if ($entity->getArmorInventory()->getBoots()->getEnchantment(Enchantment::get(CustomEnchantIds::SPRINGS)) === null) Utils::setShouldTakeFallDamage($entity, true);
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
     * @param EntityEffectAddEvent $event
     * @priority HIGHEST
     */
    public function onEffectAdd(EntityEffectAddEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            ReactiveEnchantment::attemptReaction($entity, $event);
        }
    }

    /**
     * @param EntityShootBowEvent $event
     * @priority HIGHEST
     */
    public function onShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            ReactiveEnchantment::attemptReaction($entity, $event);
        }
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        ReactiveEnchantment::attemptReaction($player, $event);
    }

    /**
     * @param PlayerInteractEvent $event
     * @priority HIGHEST
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        ReactiveEnchantment::attemptReaction($event->getPlayer(), $event);
    }

    /**
     * @param PlayerItemHeldEvent $event
     * @priority HIGHEST
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

    /**
     * @param PlayerItemUseEvent $event
     * @priority HIGHEST
     */
    public function onItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        if ($this->plugin->getConfig()->getNested("miscellaneous.armor-hold-equip", false)) {
            if ($item instanceof Armor || $item->getId() === ItemIds::ELYTRA || $item->getId() === ItemIds::PUMPKIN || $item->getId() === ItemIds::SKULL) {
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
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot);
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot);
        }
        $player->getInventory()->addChangeListeners(new CustomEnchantToggleListener());
        $player->getArmorInventory()->addChangeListeners(new CustomEnchantToggleListener());
    }

    /**
     * @param PlayerMoveEvent $event
     * @priority HIGHEST
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if (!Utils::shouldTakeFallDamage($player)) {
            if ($player->getWorld()->getBlock($player->getPosition()->floor()->subtract(0, 1))->getId() !== BlockLegacyIds::AIR && Utils::getNoFallDamageDuration($player) <= 0) {
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
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot, false);
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot, false);
        }
    }

    /**
     * @param PlayerToggleSneakEvent $event
     * @priority HIGHEST
     */
    public function onSneak(PlayerToggleSneakEvent $event): void
    {
        $player = $event->getPlayer();
        ReactiveEnchantment::attemptReaction($player, $event);
    }

    /**
     * @param ProjectileHitBlockEvent $event
     * @priority HIGHEST
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
     * @param ProjectileLaunchEvent $event
     * @priority HIGHEST
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
     * @param InventoryTransactionEvent $event
     * @priority HIGHEST
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
            if ($itemClickedWith->getId() === ItemIds::ENCHANTED_BOOK && $itemClicked->getId() !== ItemIds::AIR) {
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
                    $oldToNew->getInventory()->setItem($oldToNew->getSlot(), ItemFactory::get(ItemIds::AIR));
                }
            }
        }
    }
}