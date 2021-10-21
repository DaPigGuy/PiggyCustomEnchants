<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\ToggleableEnchantment;
use DaPigGuy\PiggyCustomEnchants\enchants\tools\DrillerEnchant;
use DaPigGuy\PiggyCustomEnchants\entities\BombardmentTNT;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use DaPigGuy\PiggyCustomEnchants\utils\ProjectileTracker;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\block\BlockLegacyIds;
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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\player\Player;

class EventListener implements Listener
{
    private PiggyCustomEnchants $plugin;

    public function __construct(PiggyCustomEnchants $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @priority HIGHEST
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
            $transaction = $packet->trData;
            foreach ($transaction->getActions() as $key => $action) {
                $action->oldItem = new ItemStackWrapper($action->oldItem->getStackId(), Utils::filterDisplayedEnchants($action->oldItem->getItemStack()));
                $action->newItem = new ItemStackWrapper($action->newItem->getStackId(), Utils::filterDisplayedEnchants($action->newItem->getItemStack()));
            }
            if ($transaction instanceof UseItemTransactionData) {
                if ($transaction->getActionType() === UseItemTransactionData::ACTION_BREAK_BLOCK) {
                    DrillerEnchant::$lastBreakFace[$event->getOrigin()->getPlayer()->getName()] = $transaction->getFace();
                }
            }
        }
        if ($packet instanceof MobEquipmentPacket) Utils::filterDisplayedEnchants($packet->item->getItemStack());
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        $packets = $event->getPackets();
        foreach ($packets as $packet) {
            if ($packet instanceof InventorySlotPacket) {
                $packet->item = new ItemStackWrapper($packet->item->getStackId(), Utils::displayEnchants($packet->item->getItemStack()));
            }
            if ($packet instanceof InventoryContentPacket) {
                foreach ($packet->items as $i => $item) {
                    $packet->items[$i] = new ItemStackWrapper($item->getStackId(), Utils::displayEnchants($item->getItemStack()));
                }
            }
        }
    }

    public function onBlockChange(EntityBlockChangeEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof BombardmentTNT) {
            for ($i = 0; $i < 3 + $entity->getEnchantmentLevel(); $i++) {
                $tnt = new PiggyTNT($entity->getLocation(), null, $this->plugin->getConfig()->getNested("world-damage.missile", false));
                $tnt->setFuse(0);
                $tnt->setOwningEntity($entity->getOwningEntity());
                $tnt->spawnToAll();
            }
            $event->setCancelled();
        }
    }

    /**
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
            if ($attacker instanceof Player) ReactiveEnchantment::attemptReaction($attacker, $event);
        }
    }

    /**
     * @priority HIGHEST
     */
    public function onEffectAdd(EntityEffectAddEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) ReactiveEnchantment::attemptReaction($entity, $event);
    }

    /**
     * @priority HIGHEST
     */
    public function onShootBow(EntityShootBowEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) ReactiveEnchantment::attemptReaction($entity, $event);
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        ReactiveEnchantment::attemptReaction($event->getPlayer(), $event);
    }

    /**
     * @priority HIGHEST
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        ReactiveEnchantment::attemptReaction($event->getPlayer(), $event);
    }

    /**
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

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        foreach ($player->getInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) {
                ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getInventory(), $slot);
            }
        }
        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            foreach ($content->getEnchantments() as $enchantmentInstance) {
                ToggleableEnchantment::attemptToggle($player, $content, $enchantmentInstance, $player->getArmorInventory(), $slot);
            }
        }

        $onSlot = function (Inventory $inventory, int $slot, Item $oldItem): void {
            if ($inventory instanceof PlayerInventory || $inventory instanceof ArmorInventory) {
                $holder = $inventory->getHolder();
                if ($holder instanceof Player) {
                    if (!$oldItem->equals(($newItem = $inventory->getItem($slot)), !$inventory instanceof ArmorInventory)) {
                        if ($newItem->getId() === ItemIds::AIR || $inventory instanceof ArmorInventory) foreach ($oldItem->getEnchantments() as $oldEnchantment) ToggleableEnchantment::attemptToggle($holder, $oldItem, $oldEnchantment, $inventory, $slot, false);
                        if ($oldItem->getId() === ItemIds::AIR || $inventory instanceof ArmorInventory) foreach ($newItem->getEnchantments() as $newEnchantment) ToggleableEnchantment::attemptToggle($holder, $newItem, $newEnchantment, $inventory, $slot);
                    }
                }
            }
        };
        /**
         * @param Item[] $oldContents
         */
        $onContent = function (Inventory $inventory, array $oldContents) use ($onSlot): void {
            foreach ($oldContents as $slot => $oldItem) {
                if (!($oldItem ?? ItemFactory::air())->equals($inventory->getItem($slot), !$inventory instanceof ArmorInventory)) {
                    $onSlot($inventory, $slot, $oldItem);
                }
            }
        };
        $player->getInventory()->getListeners()->add(new CallbackInventoryListener($onSlot, $onContent));
        $player->getArmorInventory()->getListeners()->add(new CallbackInventoryListener($onSlot, $onContent));
    }

    /**
     * @priority HIGHEST
     */
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        if (!Utils::shouldTakeFallDamage($player)) {
            if ($player->getWorld()->getBlock($player->getPosition()->floor()->subtract(0, 1, 0))->getId() !== BlockLegacyIds::AIR && Utils::getNoFallDamageDuration($player) <= 0) {
                Utils::setShouldTakeFallDamage($player, true);
            } else {
                Utils::increaseNoFallDamageDuration($player);
            }
        }
        if ($event->getFrom()->floor()->equals($event->getTo()->floor())) return;
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
     * @priority HIGHEST
     */
    public function onSneak(PlayerToggleSneakEvent $event): void
    {
        ReactiveEnchantment::attemptReaction($event->getPlayer(), $event);
    }

    /**
     * @priority HIGHEST
     */
    public function onProjectileHitBlock(ProjectileHitBlockEvent $event): void
    {
        $shooter = $event->getEntity()->getOwningEntity();
        if ($shooter instanceof Player) ReactiveEnchantment::attemptReaction($shooter, $event);
    }

    /**
     * @priority HIGHEST
     */
    public function onProjectileLaunch(ProjectileLaunchEvent $event): void
    {
        $projectile = $event->getEntity();
        $shooter = $projectile->getOwningEntity();
        if ($shooter instanceof Player) ProjectileTracker::addProjectile($projectile, $shooter->getInventory()->getItemInHand());
    }

    /**
     * @priority HIGHEST
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
                        if (($existingEnchant = $itemClicked->getEnchantment($enchantment->getType())) !== null) {
                            if ($existingEnchant->getLevel() > $newLevel) continue;
                            $newLevel = $existingEnchant->getLevel() === $newLevel ? $newLevel + 1 : $newLevel;
                        }
                        if (!Utils::canBeEnchanted($itemClicked, $enchantment->getType(), $newLevel) || ($itemClicked->getId() === ItemIds::ENCHANTED_BOOK && count($itemClicked->getEnchantments()) === 0)) continue;
                        $itemClicked->addEnchantment(new EnchantmentInstance($enchantment->getType(), $enchantment->getLevel()));
                        $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                        $enchantmentSuccessful = true;
                    }
                    if ($enchantmentSuccessful) {
                        $event->cancel();
                        $otherAction->getInventory()->setItem($otherAction->getSlot(), ItemFactory::air());
                    }
                }
            }
        }
    }
}