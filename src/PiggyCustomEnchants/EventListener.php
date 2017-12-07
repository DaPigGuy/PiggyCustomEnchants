<?php

namespace PiggyCustomEnchants;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Entities\Fireball;
use PiggyCustomEnchants\Entities\PigProjectile;
use PiggyCustomEnchants\Tasks\GoeyTask;
use PiggyCustomEnchants\Tasks\GrapplingTask;
use PiggyCustomEnchants\Tasks\HallucinationTask;
use PiggyCustomEnchants\Tasks\ImplantsTask;
use PiggyCustomEnchants\Tasks\MoltenTask;
use PiggyCustomEnchants\Tasks\PlaceTask;
use pocketmine\block\Block;
use pocketmine\block\Crops;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\Player;
use pocketmine\utils\Random;
use pocketmine\utils\TextFormat;

/**
 * Class EventListener
 * @package PiggyCustomEnchants
 */
class EventListener implements Listener
{
    private $plugin;

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param BlockBreakEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $this->checkToolEnchants($player, $event);
    }

    /**
     * @param EntityArmorChangeEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onArmorChange(EntityArmorChangeEvent $event)
    {
        $entity = $event->getEntity();
        $this->checkArmorEnchants($entity, $event);
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     * @return bool
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();
        $this->checkArmorEnchants($entity, $event);
        if ($cause == EntityDamageEvent::CAUSE_FALL && $entity instanceof Player && (isset($this->plugin->nofall[strtolower($entity->getName())]) || isset($this->plugin->flying[strtolower($entity->getName())]))) {
            unset($this->plugin->nofall[strtolower($entity->getName())]);
            $event->setCancelled();
        }
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $damager = $event->getDamager();
            $child = $event->getChild();
            if ($damager instanceof Player && $child instanceof Projectile) {
                $this->checkGlobalEnchants($damager, $entity, $event);
                $this->checkBowEnchants($damager, $entity, $event);
            }
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if ($damager instanceof Player) {
                if ($damager->getInventory()->getItemInHand()->getId() == Item::BOW) { //TODO: Move to canUse() function
                    return false;
                }
                $this->checkGlobalEnchants($damager, $entity, $event);
            }
        }
        return true;
    }

    /**
     * @param EntityShootBowEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onShoot(EntityShootBowEvent $event)
    {
        $shooter = $event->getEntity();
        $entity = $event->getProjectile();
        if ($shooter instanceof Player) {
            $this->checkBowEnchants($shooter, $entity, $event);
        }
    }

    /***
     * @param InventoryPickupArrowEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onPickupArrow(InventoryPickupArrowEvent $event)
    {
        $arrow = $event->getArrow();
        if ($arrow->namedtag->hasTag("Volley")) {
            $event->setCancelled();
        }
    }

    /**
     * @param PlayerDeathEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        $this->checkGlobalEnchants($player, null, $event);
    }

    /**
     * Disable movement being reverted when flying with a Jetpack
     *
     * @param PlayerIllegalMoveEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onIllegalMove(PlayerIllegalMoveEvent $event)
    {
        $player = $event->getPlayer();
        if (isset($this->plugin->flying[strtolower($player->getName())]) || $this->plugin->getEnchantment($player->getInventory()->getChestplate(), CustomEnchants::SPIDER) !== null) {
            $event->setCancelled();
        }
    }

    /**
     * @param PlayerInteractEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $this->checkToolEnchants($player, $event);
    }

    /**
     * Disable kicking for flying when using jetpacks
     *
     * @param PlayerKickEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onKick(PlayerKickEvent $event)
    {
        $player = $event->getPlayer();
        $reason = $event->getReason();
        if ($reason == "Flying is not enabled on this server") {
            if (isset($this->plugin->flying[strtolower($player->getName())]) || $this->plugin->getEnchantment($player->getInventory()->getChestplate(), CustomEnchants::SPIDER) !== null) {
                $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     * @return bool
     */
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        if (isset($this->plugin->nofall[strtolower($player->getName())])) {
            if ($this->plugin->checkBlocks($player, 0, 1) !== true && $this->plugin->nofall[strtolower($player->getName())] < time()) {
                unset($this->plugin->nofall[strtolower($player->getName())]);
            } else {
                $this->plugin->nofall[strtolower($player->getName())]++;
            }
        }
        if ($from->getFloorX() == $player->getFloorX() && $from->getFloorY() == $player->getFloorY() && $from->getFloorZ() == $player->getFloorZ()) {
            return false;
        }
        $this->checkGlobalEnchants($player, null, $event);
        $this->checkArmorEnchants($player, $event);
        return true;
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if (isset($this->plugin->blockface[$name])) {
            unset($this->plugin->blockface[$name]);
        }
        if (isset($this->plugin->glowing[$name])) {
            unset($this->plugin->glowing[$name]);
        }
        if (isset($this->plugin->grew[$name])) {
            unset($this->plugin->grew[$name]);
        }
        if (isset($this->plugin->flying[$name])) {
            unset($this->plugin->flying[$name]);
        }
        if (isset($this->plugin->hallucination[$name])) {
            unset($this->plugin->hallucination[$name]);
        }
        if (isset($this->plugin->implants[$name])) {
            unset($this->plugin->implants[$name]);
        }
        if (isset($this->plugin->mined[$name])) {
            unset($this->plugin->mined[$name]);
        }
        if (isset($this->plugin->nofall[$name])) {
            unset($this->plugin->nofall[$name]);
        }
        for ($i = 0; $i <= 3; $i++) {
            if (isset($this->plugin->overload[$name . "||" . $i])) {
                unset($this->plugin->overload[$name . "||" . $i]);
            }
        }
        if (isset($this->plugin->prowl[$name])) {
            unset($this->plugin->prowl[$name]);
        }
        if (isset($this->plugin->using[$name])) {
            unset($this->plugin->using[$name]);
        }
        if (isset($this->plugin->shrunk[$name])) {
            unset($this->plugin->shrunk[$name]);
        }
    }

    /**
     * @param PlayerToggleSneakEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onSneak(PlayerToggleSneakEvent $event)
    {
        $player = $event->getPlayer();
        if ($event->isSneaking()) {
            $this->checkArmorEnchants($player, $event);
        }
    }

    /**
     * @param ProjectileHitEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onHit(ProjectileHitEvent $event)
    {
        $entity = $event->getEntity();
        $shooter = $entity->getOwningEntity();
        if ($shooter instanceof Player) {
            $this->checkBowEnchants($shooter, $entity, $event);
        }
    }

    /**
     * @param DataPacketReceiveEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event)
    {
        $player = $event->getPlayer();
        $packet = $event->getPacket();
        if ($packet instanceof PlayerActionPacket) {
            $action = $packet->action;
            switch ($action) {
                case PlayerActionPacket::ACTION_JUMP:
                    $this->checkArmorEnchants($player, $event);
                    break;
                case PlayerActionPacket::ACTION_CONTINUE_BREAK:
                    $this->plugin->blockface[strtolower($player->getName())] = $packet->face;
                    break;
            }
        }
    }

    /**
     * @param Player $damager
     * @param Entity $entity
     * @param EntityEvent|Event $event
     */
    public function checkGlobalEnchants(Player $damager, Entity $entity = null, Event $event)
    {
        //TODO: Check to make sure you can use enchant with item
        if ($event instanceof EntityDamageEvent) {
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::LIFESTEAL);
            if ($enchantment !== null) {
                if ($damager->getHealth() + 2 + $enchantment->getLevel() <= $damager->getMaxHealth()) {
                    $damager->setHealth($damager->getHealth() + 2 + $enchantment->getLevel());
                } else {
                    $damager->setHealth($damager->getMaxHealth());
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::BLIND);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::BLINDNESS);
                $effect->setAmplifier(0);
                $effect->setDuration(50 + 20 * $enchantment->getLevel());
                $effect->setVisible(false);
                $entity->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::DEATHBRINGER);
            if ($enchantment !== null) {
                $damage = 2 + ($enchantment->getLevel() / 10);
                $event->setDamage($event->getDamage() + $damage);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::GOOEY);
            if ($enchantment !== null) {
                $task = new GoeyTask($this->plugin, $entity, $enchantment->getLevel());
                $this->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 1);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::POISON);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::POISON);
                $effect->setAmplifier($enchantment->getLevel());
                $effect->setDuration(30 * $enchantment->getLevel());
                $effect->setVisible(false);
                $entity->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::CRIPPLINGSTRIKE);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::NAUSEA);
                $effect->setAmplifier(0);
                $effect->setDuration(50 * $enchantment->getLevel());
                $effect->setVisible(false);
                $entity->addEffect($effect);
                $effect = Effect::getEffect(Effect::SLOWNESS);
                $effect->setAmplifier($enchantment->getLevel());
                $effect->setDuration(50 * $enchantment->getLevel());
                $effect->setVisible(false);
                $entity->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::VAMPIRE);
            if ($enchantment !== null) {
                if (!isset($this->plugin->vampirecd[strtolower($damager->getName())]) || time() > $this->plugin->vampirecd[strtolower($damager->getName())]) {
                    $this->plugin->vampirecd[strtolower($damager->getName())] = time() + 5;
                    if ($damager->getHealth() + ($event->getDamage() / 2) <= $damager->getMaxHealth()) {
                        $damager->setHealth($damager->getHealth() + ($event->getDamage() / 2));
                    } else {
                        $damager->setHealth($damager->getMaxHealth());
                    }
                    if ($damager->getFood() + ($event->getDamage() / 2) <= $damager->getMaxFood()) {
                        $damager->setFood($damager->getFood() + ($event->getDamage() / 2));
                    } else {
                        $damager->setFood($damager->getMaxFood());
                    }
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::CHARGE);
            if ($enchantment !== null) {
                if ($damager->isSprinting()) {
                    $event->setDamage($event->getDamage() * (1 + 0.10 * $enchantment->getLevel()));
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::AERIAL);
            if ($enchantment !== null) {
                if (!$damager->isOnGround()) {
                    $event->setDamage($event->getDamage() * (1 + 0.10 * $enchantment->getLevel()));
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::WITHER);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::WITHER);
                $effect->setAmplifier($enchantment->getLevel());
                $effect->setDuration(40 * $enchantment->getLevel());
                $effect->setVisible(false);
                $entity->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::DISARMING);
            if ($enchantment !== null) {
                if ($entity instanceof Player) {
                    $chance = 5 * $enchantment->getLevel();
                    $random = mt_rand(0, 100);
                    if ($random <= $chance) {
                        $item = $entity->getInventory()->getItemInHand();
                        $entity->getInventory()->removeItem($item);
                        $motion = $entity->getDirectionVector()->multiply(0.4);
                        $entity->getLevel()->dropItem($entity->add(0, 1.3, 0), $item, $motion, 40);
                    }
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::HALLUCINATION);
            if ($enchantment !== null) {
                $chance = 5 * $enchantment->getLevel();
                $random = mt_rand(0, 100);
                if ($random <= $chance && isset($this->plugin->hallucination[strtolower($entity->getName())]) !== true && $entity instanceof Player) {
                    $this->plugin->hallucination[strtolower($entity->getName())] = true;
                    $task = new HallucinationTask($this->plugin, $entity, $entity->getPosition());
                    $handler = $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($task, 1);
                    $task->setHandler($handler);
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::BLESSED);
            if ($enchantment !== null) {
                $chance = 15 * $enchantment->getLevel();
                $random = mt_rand(0, 100);
                if ($random <= $chance) {
                    foreach ($damager->getEffects() as $effect) {
                        if ($effect->isBad()) {
                            $damager->removeEffect($effect->getId());
                        }
                    }
                }
            }
        }
        if ($event instanceof PlayerDeathEvent) {
            $drops = $event->getDrops();
            $soulbounded = [];
            $soulboundedarmor = [];
            foreach ($damager->getInventory()->getContents() as $k => $item) {
                $enchantment = $this->plugin->getEnchantment($item, CustomEnchants::SOULBOUND);
                if ($enchantment !== null) {
                    $index = array_search($item, $drops);
                    if ($index !== false) {
                        unset($drops[$index]);
                    }
                    $soulbounded[$k] = $this->plugin->removeEnchantment($item, $enchantment);
                }
            }
            foreach ($damager->getInventory()->getArmorContents() as $k => $item) {
                $enchantment = $this->plugin->getEnchantment($item, CustomEnchants::SOULBOUND);
                if ($enchantment !== null) {
                    $index = array_search($item, $drops);
                    if ($index !== false) {
                        unset($drops[$index]);
                    }
                    $soulboundedarmor[$k] = $this->plugin->removeEnchantment($item, $enchantment);
                }
            }
            $event->setDrops([]);
            $event->setKeepInventory(true);
            foreach ($drops as $drop) {
                $damager->getLevel()->dropItem($damager, $drop);
            }
            $damager->getInventory()->setArmorContents($soulboundedarmor);
            $damager->getInventory()->setContents($soulbounded);
        }
        if ($event instanceof PlayerMoveEvent) {
            foreach ($damager->getInventory()->getContents() as $slot => $item) {
                $enchantment = $this->plugin->getEnchantment($item, CustomEnchants::AUTOREPAIR);
                if ($enchantment !== null) {
                    $newDir = $item->getDamage() - (1 + (1 * $enchantment->getLevel()));
                    if ($newDir < 0) {
                        $item->setDamage(0);
                    } else {
                        $item->setDamage($newDir);
                    }
                    $damager->getInventory()->setItem($slot, $item);
                }
            }
        }
    }

    /**
     * @param Player $player
     * @param Event $event
     */
    public function checkToolEnchants(Player $player, Event $event)
    {
        if ($event instanceof BlockBreakEvent) {
            $block = $event->getBlock();
            $drops = $event->getDrops();
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::EXPLOSIVE);
            if ($enchantment !== null) {
                $explosion = new Explosion($block, $enchantment->getLevel() * 5, $player);
                $explosion->explodeA();
                $explosion->explodeB();
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::LUMBERJACK);
            if ($enchantment !== null) {
                if ($player->isSneaking()) {
                    if ($block->getId() == Block::WOOD || $block->getId() == Block::WOOD2) {
                        if (!isset($this->plugin->using[strtolower($player->getName())]) || $this->plugin->using[strtolower($player->getName())] < time()) {
                            $this->plugin->mined[strtolower($player->getName())] = 0;
                            $this->breakTree($block, $player);
                        }
                    }
                }
                $event->setInstaBreak(true);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::DRILLER);
            if ($enchantment !== null) {
                if (!isset($this->plugin->using[strtolower($player->getName())]) || $this->plugin->using[strtolower($player->getName())] < time()) {
                    if (isset($this->plugin->blockface[strtolower($player->getName())])) {
                        $side = $this->plugin->blockface[strtolower($player->getName())];
                        $sides = [];
                        $sides2 = [];
                        switch ($side) {
                            case Vector3::SIDE_NORTH:
                            case Vector3::SIDE_SOUTH:
                                $sides = [Vector3::SIDE_WEST, Vector3::SIDE_EAST, Vector3::SIDE_UP, Vector3::SIDE_DOWN];
                                $sides2 = [Vector3::SIDE_DOWN, Vector3::SIDE_UP, Vector3::SIDE_EAST, Vector3::SIDE_WEST];
                                break;
                            case Vector3::SIDE_WEST:
                            case Vector3::SIDE_EAST:
                                $sides = [Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];
                                $sides2 = [Vector3::SIDE_DOWN, Vector3::SIDE_UP, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];
                                break;
                            case Vector3::SIDE_UP:
                            case Vector3::SIDE_DOWN:
                                $sides = [Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, Vector3::SIDE_EAST];
                                $sides2 = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];
                                break;
                        }
                        $item = $player->getInventory()->getItemInHand();
                        for ($i = 0; $i <= $enchantment->getLevel(); $i++) {
                            $b = $block->getSide($side ^ 0x01, $i);
                            $combined = array_combine($sides, $sides2);
                            $this->plugin->using[strtolower($player->getName())] = time() + 1;
                            $player->getLevel()->useBreakOn($b, $item, $player);
                            foreach ($sides as $s) {
                                $b2 = $b->getSide($s, 1);
                                $b3 = $b2->getSide($combined[$s], 1);
                                $b4 = $b2->getSide($combined[$s] ^ 0x01, 1);
                                $player->getLevel()->useBreakOn($b2, $item, $player);
                                $player->getLevel()->useBreakOn($b3, $item, $player);
                                $player->getLevel()->useBreakOn($b4, $item, $player);

                            }
                        }
                        unset($this->plugin->blockface[strtolower($player->getName())]);
                    }
                }
                $event->setInstaBreak(true);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::SMELTING);
            if ($enchantment !== null) {
                $finaldrop = array();
                $otherdrops = array();
                foreach ($drops as $drop) {
                    switch ($drop->getId()) {
                        case Item::COBBLESTONE:
                            array_push($finaldrop, Item::get(Item::STONE, 0, $drop->getCount()));
                            break;
                        case Item::IRON_ORE:
                            array_push($finaldrop, Item::get(Item::IRON_INGOT, 0, $drop->getCount()));
                            break;
                        case Item::GOLD_ORE:
                            array_push($finaldrop, Item::get(Item::GOLD_INGOT, 0, $drop->getCount()));
                            break;
                        case Item::SAND:
                            array_push($finaldrop, Item::get(Item::GLASS, 0, $drop->getCount()));
                            break;
                        case Item::CLAY:
                            array_push($finaldrop, Item::get(Item::BRICK, 0, $drop->getCount()));
                            break;
                        case Item::NETHERRACK:
                            array_push($finaldrop, Item::get(Item::NETHER_BRICK, 0, $drop->getCount()));
                            break;
                        case Item::STONE_BRICK:
                            if ($drop->getDamage() == 0) {
                                array_push($finaldrop, Item::get(Item::STONE_BRICK, 2, $drop->getCount()));
                            }
                            break;
                        case Item::CACTUS:
                            array_push($finaldrop, Item::get(Item::DYE, 2, $drop->getCount()));
                            break;
                        case Item::WOOD:
                        case Item::WOOD2:
                            array_push($finaldrop, Item::get(Item::COAL, 1, $drop->getCount()));
                            break;
                        case Item::SPONGE:
                            if ($drop->getDamage() == 1) {
                                array_push($finaldrop, Item::get(Item::SPONGE, 0, $drop->getCount()));
                            }
                            break;
                        default:
                            array_push($otherdrops, $drop);
                            break;
                    }
                }
                $event->setDrops($drops = array_merge($finaldrop, $otherdrops));
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::ENERGIZING);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::HASTE);
                $effect->setAmplifier(1 + $enchantment->getLevel() - 2);
                $effect->setDuration(20);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::QUICKENING);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::SPEED);
                $effect->setAmplifier(3 + $enchantment->getLevel() - 2);
                $effect->setDuration(50);
                $effect->setVisible(false);
                $player->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::TELEPATHY);
            if ($enchantment !== null) {
                foreach ($drops as $drop) {
                    $player->getInventory()->addItem($drop);
                }
                $event->setDrops([]);
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::FARMER);
            if ($enchantment !== null) {
                $seed = null;
                switch ($block->getId()) {
                    case Block::WHEAT_BLOCK:
                        $seed = Item::SEEDS;
                        break;
                    case Block::POTATO_BLOCK:
                        $seed = Item::POTATO;
                        break;
                    case Block::CARROT_BLOCK:
                        $seed = Item::CARROT;
                        break;
                    case Block::BEETROOT_BLOCK:
                        $seed = Item::BEETROOT_SEEDS;
                        break;
                }
                if ($seed !== null) {
                    $seed = Item::get($seed, 0, 1);
                    $pos = $block->subtract(0, 1);
                    $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new PlaceTask($this->plugin, $pos, $block->getLevel(), $seed, $player), 1);
                }
            }
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::HARVEST);
            if ($enchantment !== null) {
                $radius = $enchantment->getLevel();
                if (!isset($this->plugin->using[strtolower($player->getName())]) || $this->plugin->using[strtolower($player->getName())] < time()) {
                    if ($block instanceof Crops) {
                        for ($x = -$radius; $x <= $radius; $x++) {
                            for ($z = -$radius; $z <= $radius; $z++) {
                                $pos = $block->add($x, 0, $z);
                                if ($block->getLevel()->getBlock($pos) instanceof Crops) {
                                    $this->plugin->using[strtolower($player->getName())] = time() + 1;
                                    $item = $player->getInventory()->getItemInHand();
                                    $block->getLevel()->useBreakOn($pos, $item, $player);
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($event instanceof PlayerInteractEvent) {
            $block = $event->getBlock();
            $enchantment = $this->plugin->getEnchantment($player->getInventory()->getItemInHand(), CustomEnchants::FERTILIZER);
            if ($enchantment !== null) {
                if (!isset($this->plugin->using[strtolower($player->getName())]) || $this->plugin->using[strtolower($player->getName())] < time()) {
                    if ($this->plugin->checkBlocks($block, [Block::DIRT, Block::GRASS])) {
                        $radius = $enchantment->getLevel();
                        for ($x = -$radius; $x <= $radius; $x++) {
                            for ($z = -$radius; $z <= $radius; $z++) {
                                $pos = $block->add($x, 0, $z);
                                if ($this->plugin->checkBlocks(Position::fromObject($pos, $block->getLevel()), [Block::DIRT, Block::GRASS])) {
                                    $this->plugin->using[strtolower($player->getName())] = time() + 1;
                                    $item = $player->getInventory()->getItemInHand();
                                    $block->getLevel()->useItemOn($pos, $item, 0, $pos, $player);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Player $damager
     * @param Entity $entity
     * @param EntityEvent $event
     */
    public function checkBowEnchants(Player $damager, Entity $entity, EntityEvent $event)
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::MOLOTOV);
            if ($enchantment !== null) {
                $boundaries = 0.1 * $enchantment->getLevel();
                for ($x = $boundaries; $x >= -$boundaries; $x -= 0.1) {
                    for ($z = $boundaries; $z >= -$boundaries; $z -= 0.1) {
                        $entity->getLevel()->setBlock($entity->add(0, 1), Block::get(Block::FIRE));
                        $fire = Entity::createEntity("FallingSand", $entity->getLevel(), new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $entity->x + 0.5), new DoubleTag("", $entity->y + 1), new DoubleTag("", $entity->z + 0.5)]), "Motion" => new ListTag("Motion", [new DoubleTag("", $x), new DoubleTag("", 0.1), new DoubleTag("", $z)]), "Rotation" => new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]), "TileID" => new IntTag("TileID", 51), "Data" => new ByteTag("Data", 0)]));
                        $fire->spawnToAll();
                    }
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::PARALYZE);
            if ($enchantment !== null) {
                $effect = Effect::getEffect(Effect::SLOWNESS);
                $effect->setAmplifier(5 + $enchantment->getLevel() - 1);
                $effect->setDuration(60 + ($enchantment->getLevel() - 1) * 20);
                $effect->setVisible(false);
                $entity->addEffect($effect);
                $effect = Effect::getEffect(Effect::BLINDNESS);
                $effect->setAmplifier(1);
                $effect->setDuration(60 + ($enchantment->getLevel() - 1) * 20);
                $effect->setVisible(false);
                $entity->addEffect($effect);
                $effect = Effect::getEffect(Effect::WEAKNESS);
                $effect->setAmplifier(5 + $enchantment->getLevel() - 1);
                $effect->setDuration(60 + ($enchantment->getLevel() - 1) * 20);
                $effect->setVisible(false);
                $entity->addEffect($effect);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::PIERCING);
            if ($enchantment !== null) {
                $event->setDamage(0, EntityDamageEvent::MODIFIER_ARMOR);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::SHUFFLE);
            if ($enchantment !== null) {
                $pos1 = clone $damager->getPosition();
                $pos2 = clone $entity->getPosition();
                $damager->teleport($pos2);
                $entity->teleport($pos1);
                $name = $entity->getNameTag();
                if ($entity instanceof Player) {
                    $name = $entity->getDisplayName();
                    $entity->sendMessage(TextFormat::DARK_PURPLE . "You have switched positions with " . $damager->getDisplayName());
                }
                $damager->sendMessage(TextFormat::DARK_PURPLE . "You have switched positions with " . $name);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::BOUNTYHUNTER);
            if ($enchantment !== null) {
                if (!isset($this->plugin->bountyhuntercd[strtolower($damager->getName())]) || time() > $this->plugin->bountyhuntercd[strtolower($damager->getName())]) {
                    $bountydrop = $this->getBounty();
                    $damager->getInventory()->addItem(Item::get($bountydrop, 0, mt_rand(0, 8 + $enchantment->getLevel()) + 1));
                    $this->plugin->bountyhuntercd[strtolower($damager->getName())] = time() + 30;
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::HEALING);
            if ($enchantment !== null) {
                if ($entity->getHealth() + $event->getDamage() + $enchantment->getLevel() <= $entity->getMaxHealth()) {
                    $entity->setHealth($entity->getHealth() + $event->getDamage() + $enchantment->getLevel());
                } else {
                    $entity->setHealth($entity->getMaxHealth());
                }
                $event->setDamage(0);
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::HEADHUNTER);
            if ($enchantment !== null) {
                $projectile = $event->getChild();
                if ($projectile->y > $entity->getPosition()->y + $entity->getEyeHeight()) {
                    $event->setDamage($event->getDamage() * (1 + 0.10 * $enchantment->getLevel()));
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::GRAPPLING);
            if ($enchantment !== null) {
                $task = new GrapplingTask($this->plugin, $damager->getPosition(), $entity);
                $this->plugin->getServer()->getScheduler()->scheduleDelayedTask($task, 1); //Delayed due to knockback interfering
            }
        }
        if ($event instanceof EntityShootBowEvent) {
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::BLAZE);
            if ($enchantment !== null && $entity instanceof Fireball !== true) {
                $nbt = Entity::createBaseNBT($entity, $damager->getDirectionVector(), $entity->yaw, $entity->pitch);
                $fireball = Entity::createEntity("Fireball", $damager->getLevel(), $nbt, $damager);
                $fireball->setMotion($fireball->getMotion()->multiply($event->getForce()));
                $fireball->spawnToAll();
                $entity->close();
                $entity = $fireball;
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::PORKIFIED);
            if ($enchantment !== null && $entity instanceof PigProjectile !== true) {
                $nbt = Entity::createBaseNBT($entity, $damager->getDirectionVector(), $entity->yaw, $entity->pitch);
                $pig = Entity::createEntity("PigProjectile", $damager->getLevel(), $nbt, $damager, $enchantment->getLevel());
                $pig->setMotion($pig->getMotion()->multiply($event->getForce()));
                $pig->spawnToAll();
                $entity->close();
                $entity = $pig;
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::VOLLEY);
            if ($enchantment !== null) {
                $amount = 1 + 2 * $enchantment->getLevel();
                $anglesbetweenarrows = (45 / ($amount - 1)) * M_PI / 180;
                $pitch = ($damager->getLocation()->getPitch() + 90) * M_PI / 180;
                $yaw = ($damager->getLocation()->getYaw() + 90 - 45 / 2) * M_PI / 180;
                $sZ = cos($pitch);
                for ($i = 0; $i < $amount; $i++) {
                    $nX = sin($pitch) * cos($yaw + $anglesbetweenarrows * $i);
                    $nY = sin($pitch) * sin($yaw + $anglesbetweenarrows * $i);
                    $newDir = new Vector3($nX, $sZ, $nY);
                    $projectile = null;
                    if ($entity instanceof Arrow) {
                        $nbt = Entity::createBaseNBT($damager->add(0, $damager->getEyeHeight()), $damager->getDirectionVector(), $damager->yaw, $damager->pitch);
                        $nbt->setTag(new ByteTag("Volley", 1));
                        $projectile = Entity::createEntity("Arrow", $damager->getLevel(), $nbt, $damager, $entity->isCritical());
                    }
                    if ($entity instanceof Fireball) {
                        $nbt = Entity::createBaseNBT($damager->add(0, $damager->getEyeHeight()), $damager->getDirectionVector(), $damager->yaw, $damager->pitch);
                        $projectile = Entity::createEntity("Fireball", $damager->getLevel(), $nbt, $damager);
                    }
                    if ($entity instanceof PigProjectile) {
                        $nbt = Entity::createBaseNBT($damager->add(0, $damager->getEyeHeight()), $damager->getDirectionVector(), $damager->yaw, $damager->pitch);
                        $projectile = Entity::createEntity("PigProjectile", $damager->getLevel(), $nbt, $damager, $entity->getPorkLevel());
                    }
                    $projectile->setMotion($newDir->normalize()->multiply($entity->getMotion()->multiply($event->getForce())->length()));
                    if ($projectile->isOnFire()) {
                        $projectile->setOnFire($entity->fireTicks * 20);
                    }
                    $projectile->spawnToAll();
                }
                $entity->close();
            }
        }
        if ($event instanceof ProjectileHitEvent && $entity instanceof Projectile) {
            if ($entity->hadCollision) {
                $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::GRAPPLING);
                if ($enchantment !== null) {
                    $location = $entity->getPosition();
                    $damagerloc = $damager->getPosition();
                    if ($damager->distance($entity) < 6) {
                        if ($location->y > $damager->y) {
                            $damager->setMotion(new Vector3(0, 0.25, 0));
                        } else {
                            $v = $location->subtract($damagerloc);
                            $damager->setMotion($v);
                        }
                    } else {
                        $g = -0.08;
                        $d = $location->distance($damagerloc);
                        $t = $d;
                        $v_x = (1.0 + 0.07 * $t) * ($location->x - $damagerloc->x) / $t;
                        $v_y = (1.0 + 0.03 * $t) * ($location->y - $damagerloc->y) / $t - 0.5 * $g * $t;
                        $v_z = (1.0 + 0.07 * $t) * ($location->z - $damagerloc->z) / $t;
                        $v = $damager->getMotion();
                        $v->setComponents($v_x, $v_y, $v_z);
                        $damager->setMotion($v);
                    }
                    $this->plugin->nofall[strtolower($damager->getName())] = time() + 1;
                }
            }
            $enchantment = $this->plugin->getEnchantment($damager->getInventory()->getItemInHand(), CustomEnchants::MISSILE);
            if ($enchantment !== null) {
                for ($i = 0; $i <= $enchantment->getLevel(); $i++) {
                    $tnt = Entity::createEntity("PrimedTNT", $entity->getLevel(), new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $entity->x), new DoubleTag("", $entity->y), new DoubleTag("", $entity->z)]), "Motion" => new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]), "Rotation" => new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]), "Fuse" => new ByteTag("Fuse", 40)]));
                    $tnt->spawnToAll();
                    $entity->close();
                }
            }
        }
    }

    /**
     * @param Entity $entity
     * @param EntityEvent|Event $event
     */
    public function checkArmorEnchants(Entity $entity, Event $event)
    {
        if ($entity instanceof Player) {
            $random = new Random();
            if ($event instanceof EntityDamageEvent) {
                $damage = $event->getDamage();
                $cause = $event->getCause();
                $antikb = 4;
                if ($cause == EntityDamageEvent::CAUSE_FALL) {
                    $enchantment = $this->plugin->getEnchantment($entity->getInventory()->getBoots(), CustomEnchants::STOMP);
                    if ($enchantment !== null) {
                        $entities = $entity->getLevel()->getNearbyEntities($entity->getBoundingBox());
                        foreach ($entities as $e) {
                            if ($entity === $e) {
                                continue;
                            }
                            $ev = new EntityDamageByEntityEvent($entity, $e, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage / 2);
                            $this->plugin->getServer()->getPluginManager()->callEvent($ev);
                            $e->attack($ev);
                        }
                        if (count($entities) > 1) {
                            $event->setDamage($event->getDamage() / 4);
                        }
                    }
                }
                foreach ($entity->getInventory()->getArmorContents() as $slot => $armor) {
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::SELFDESTRUCT);
                    if ($enchantment !== null) {
                        if ($event->getDamage() >= $entity->getHealth()) { //Compatibility for plugins that auto respawn players on death
                            for ($i = $enchantment->getLevel(); $i >= 0; $i--) {
                                $tnt = Entity::createEntity("PrimedTNT", $entity->getLevel(), new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $entity->x), new DoubleTag("", $entity->y), new DoubleTag("", $entity->z)]), "Motion" => new ListTag("Motion", [new DoubleTag("", $random->nextFloat() * 1.5 - 1), new DoubleTag("", $random->nextFloat() * 1.5), new DoubleTag("", $random->nextFloat() * 1.5 - 1)]), "Rotation" => new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]), "Fuse" => new ByteTag("Fuse", 40)]));
                                $tnt->spawnToAll();
                            }
                        }
                    }
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::ENDERSHIFT);
                    if ($enchantment !== null) {
                        if ($entity->getHealth() - $event->getDamage() <= 3) {
                            if (!isset($this->plugin->endershiftcd[strtolower($entity->getName())]) || time() > $this->plugin->endershiftcd[strtolower($entity->getName())]) {
                                $this->plugin->endershiftcd[strtolower($entity->getName())] = time() + 300;
                                $effect = Effect::getEffect(Effect::SPEED);
                                $effect->setAmplifier($enchantment->getLevel() + 3);
                                $effect->setDuration(100 * $enchantment->getLevel());
                                $effect->setVisible(false);
                                $entity->addEffect($effect);
                                $effect = Effect::getEffect(Effect::ABSORPTION);
                                $effect->setAmplifier($enchantment->getLevel() + 3);
                                $effect->setDuration(200 * $enchantment->getLevel());
                                $effect->setVisible(false);
                                $entity->addEffect($effect);
                                $entity->sendMessage("You feel a rush of energy coming from your armor!");
                            }
                        }
                    }
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::BERSERKER);
                    if ($enchantment !== null) {
                        if ($entity->getHealth() - $event->getDamage() <= 4) {
                            if (!isset($this->plugin->berserkercd[strtolower($entity->getName())]) || time() > $this->plugin->berserkercd[strtolower($entity->getName())]) {
                                $this->plugin->berserkercd[strtolower($entity->getName())] = time() + 300;
                                $effect = Effect::getEffect(Effect::STRENGTH);
                                $effect->setAmplifier(1 + $enchantment->getLevel());
                                $effect->setDuration(100 * $enchantment->getLevel());
                                $effect->setVisible(false);
                                $entity->addEffect($effect);
                                $entity->sendMessage("Your bloodloss makes your stronger!");
                            }
                        }
                    }
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::REVIVE);
                    if ($enchantment !== null) {
                        if ($event->getDamage() >= $entity->getHealth()) {
                            $entity->getInventory()->setArmorItem($slot, $this->plugin->removeEnchantment($armor, $enchantment));
                            $entity->removeAllEffects();
                            $entity->setHealth($entity->getMaxHealth());
                            $entity->setFood($entity->getMaxFood());
                            $event->setDamage(0);
                            //TODO: Side effect
                        }
                    }
                }
                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();
                    foreach ($entity->getInventory()->getArmorContents() as $slot => $armor) {
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::MOLTEN);
                        if ($enchantment !== null) {
                            $this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new MoltenTask($this->plugin, $damager, $enchantment->getLevel()), 1);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::ENLIGHTED);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::REGENERATION);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(50 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $entity->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::HARDENED);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::WEAKNESS);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(60 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::POISONED);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::POISON);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(40 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::FROZEN);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::SLOWNESS);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(60 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::REVULSION);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::NAUSEA);
                            $effect->setAmplifier(0);
                            $effect->setDuration(20 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::CURSED);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::WITHER);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(60 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::DRUNK);
                        if ($enchantment !== null) {
                            $effect = Effect::getEffect(Effect::SLOWNESS);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(60 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                            $effect = Effect::getEffect(Effect::MINING_FATIGUE);
                            $effect->setAmplifier($enchantment->getLevel());
                            $effect->setDuration(60 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                            $effect = Effect::getEffect(Effect::NAUSEA);
                            $effect->setAmplifier(0);
                            $effect->setDuration(60 * $enchantment->getLevel());
                            $effect->setVisible(false);
                            $damager->addEffect($effect);
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::CLOAKING);
                        if ($enchantment !== null) {
                            if (!isset($this->plugin->cloakingcd[strtolower($entity->getName())]) || time() > $this->plugin->cloakingcd[strtolower($entity->getName())]) {
                                $this->plugin->cloakingcd[strtolower($entity->getName())] = time() + 10;
                                $effect = Effect::getEffect(Effect::INVISIBILITY);
                                $effect->setAmplifier(0);
                                $effect->setDuration(50 * $enchantment->getLevel());
                                $effect->setVisible(false);
                                $entity->addEffect($effect);
                                $entity->sendMessage(TextFormat::DARK_GRAY . "You have become invisible!, for 5 seconds!");
                            }
                        }
                        $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::ANTIKNOCKBACK);
                        if ($enchantment !== null) {
                            $event->setKnockBack($event->getKnockBack() - ($event->getKnockBack() / $antikb));
                            $antikb--;
                        }
                        if ($damager instanceof Player) {
                            $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::ARMORED);
                            if ($enchantment !== null) {
                                if ($damager->getInventory()->getItemInHand()->isSword()) {
                                    $event->setDamage($damage - ($damage * 0.2 * $enchantment->getLevel()));
                                }
                            }
                            $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::TANK);
                            if ($enchantment !== null) {
                                if ($damager->getInventory()->getItemInHand()->isAxe()) {
                                    $event->setDamage($damage - ($damage * 0.2 * $enchantment->getLevel()));
                                }
                            }
                            $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::HEAVY);
                            if ($enchantment !== null) {
                                if($damager->getInventory()->getItemInHand()->getId() == Item::BOW){
                                    $event->setDamage($damage - ($damage * 0.2 * $enchantment->getLevel()));
                                }
                            }
                        }
                    }
                }
            }
            if ($event instanceof PlayerMoveEvent) {
                $enchantment = $this->plugin->getEnchantment($entity->getInventory()->getBoots(), CustomEnchants::MAGMAWALKER);
                if ($enchantment !== null) {
                    $block = $entity->getLevel()->getBlock($entity);
                    if (!$this->plugin->checkBlocks($block, [Block::STILL_LAVA, Block::LAVA, Block::FLOWING_LAVA])) {
                        $radius = $enchantment->getLevel() + 2;
                        for ($x = -$radius; $x <= $radius; $x++) {
                            for ($z = -$radius; $z <= $radius; $z++) {
                                $b = $entity->getLevel()->getBlock($entity->add($x, -1, $z));
                                if ($this->plugin->checkBlocks($b, [Block::STILL_LAVA, Block::LAVA, Block::FLOWING_LAVA])) {
                                    if ($this->plugin->checkBlocks($b, [Block::STILL_LAVA, Block::LAVA, Block::FLOWING_LAVA], -1) !== true) {
                                        if (!($b->getId() == Block::FLOWING_LAVA && $b->getDamage() > 0)) { //In vanilla, Frostwalker doesn't change non source blocks to ice
                                            $block = Block::get(Block::OBSIDIAN, 15);
                                            $entity->getLevel()->setBlock($b, $block);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $enchantment = $this->plugin->getEnchantment($entity->getInventory()->getHelmet(), CustomEnchants::MEDITATION);
                if ($enchantment !== null) {
                    if ($event->getFrom()->floor() !== $event->getTo()->floor()) {
                        $this->plugin->meditationTick[strtolower($entity->getName())] = 0;
                    }
                }
                $enchantment = $this->plugin->getEnchantment($entity->getInventory()->getHelmet(), CustomEnchants::IMPLANTS);
                if ($enchantment !== null) {
                    if ($event->getFrom()->floor() !== $event->getTo()->floor()) {
                        if (!isset($this->plugin->implantscd[strtolower($entity->getName())]) || $this->plugin->implantscd[strtolower($entity->getName())] < time()) {
                            if ($entity->getFood() < 20) {
                                $entity->setFood($entity->getFood() + $enchantment->getLevel() > 20 ? 20 : $entity->getFood() + $enchantment->getLevel());
                            }
                            if ($entity->getAirSupplyTicks() < $entity->getMaxAirSupplyTicks() && isset($this->plugin->implants[strtolower($entity->getName())]) !== true) {
                                $this->plugin->implants[strtolower($entity->getName())] = true;
                                $task = new ImplantsTask($this->plugin, $entity);
                                $handler = $this->plugin->getServer()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 60);
                                $task->setHandler($handler);
                            }
                            $this->plugin->implantscd[strtolower($entity->getName())] = time() + 1;
                        }
                    }
                }
            }
            if ($event instanceof PlayerToggleSneakEvent) {
                $shrinkpoints = 0;
                $growpoints = 0;
                $shrinklevel = 0;
                $growlevel = 0;
                foreach ($entity->getInventory()->getArmorContents() as $armor) {
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::SHRINK);
                    if ($enchantment !== null) {
                        $shrinklevel += $enchantment->getLevel();
                        $shrinkpoints++;
                    }
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchants::GROW);
                    if ($enchantment !== null) {
                        $growlevel += $enchantment->getLevel();
                        $growpoints++;
                    }
                }
                if ($shrinkpoints >= 4) {
                    if (isset($this->plugin->shrunk[strtolower($entity->getName())]) && $this->plugin->shrunk[strtolower($entity->getName())] > time()) {
                        $this->plugin->shrinkremaining[strtolower($entity->getName())] = $this->plugin->shrunk[strtolower($entity->getName())] - time();
                        unset($this->plugin->shrinkcd[strtolower($entity->getName())]);
                        unset($this->plugin->shrunk[strtolower($entity->getName())]);
                        $entity->setScale(1);
                        $entity->sendTip(TextFormat::RED . "You have grown back to normal size.");
                    } else {
                        if (!isset($this->plugin->shrinkcd[strtolower($entity->getName())]) || $this->plugin->shrinkcd[strtolower($entity->getName())] <= time()) {
                            $scale = $entity->getScale() - 0.70 - (($shrinklevel / 4) * 0.05);
                            $entity->setScale($scale);
                            $this->plugin->shrunk[strtolower($entity->getName())] = isset($this->plugin->shrinkremaining[strtolower($entity->getName())]) ? time() + $this->plugin->shrinkremaining[strtolower($entity->getName())] : time() + 60;
                            $this->plugin->shrinkcd[strtolower($entity->getName())] = isset($this->plugin->shrinkremaining[strtolower($entity->getName())]) ? time() + (75 - (60 - $this->plugin->shrinkremaining[strtolower($entity->getName())])) : time() + 75;
                            $entity->sendTip(TextFormat::GREEN . "You have shrunk. Sneak again to grow back to normal size.");
                            if (isset($this->plugin->shrinkremaining[strtolower($entity->getName())])) {
                                unset($this->plugin->shrinkremaining[strtolower($entity->getName())]);
                            }
                        }
                    }
                }
                if ($growpoints >= 4) {
                    if (isset($this->plugin->grew[strtolower($entity->getName())]) && $this->plugin->grew[strtolower($entity->getName())] > time()) {
                        $this->plugin->growremaining[strtolower($entity->getName())] = $this->plugin->grew[strtolower($entity->getName())] - time();
                        unset($this->plugin->growcd[strtolower($entity->getName())]);
                        unset($this->plugin->grew[strtolower($entity->getName())]);
                        $entity->setScale(1);
                        $entity->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
                    } else {
                        if (!isset($this->plugin->growcd[strtolower($entity->getName())]) || $this->plugin->growcd[strtolower($entity->getName())] <= time()) {
                            $scale = $entity->getScale() + 0.30 + (($growlevel / 4) * 0.05);
                            $entity->setScale($scale);
                            $this->plugin->grew[strtolower($entity->getName())] = isset($this->plugin->growremaining[strtolower($entity->getName())]) ? time() + $this->plugin->growremaining[strtolower($entity->getName())] : time() + 60;
                            $this->plugin->growcd[strtolower($entity->getName())] = isset($this->plugin->growremaining[strtolower($entity->getName())]) ? time() + (75 - (60 - $this->plugin->growremaining[strtolower($entity->getName())])) : time() + 75;
                            $entity->sendTip(TextFormat::GREEN . "You have grown. Sneak again to shrink back to normal size.");
                            if (isset($this->plugin->growremaining[strtolower($entity->getName())])) {
                                unset($this->plugin->growremaining[strtolower($entity->getName())]);
                            }
                        }
                    }
                }
                $enchantment = $this->plugin->getEnchantment($entity->getInventory()->getBoots(), CustomEnchants::JETPACK);
                if ($enchantment !== null) {
                    if (isset($this->plugin->flying[strtolower($entity->getName())]) && $this->plugin->flying[strtolower($entity->getName())] > time()) {
                        if ($entity->isOnGround()) {
                            $this->plugin->flyremaining[strtolower($entity->getName())] = $this->plugin->flying[strtolower($entity->getName())] - time();
                            unset($this->plugin->jetpackcd[strtolower($entity->getName())]);
                            unset($this->plugin->flying[strtolower($entity->getName())]);
                            $entity->sendTip(TextFormat::RED . "Jetpack disabled.");
                        } else {
                            $entity->sendTip(TextFormat::RED . "It is unsafe to disable your jetpack in the air.");
                        }
                    } else {
                        if (!in_array($event->getPlayer()->getLevel()->getName(), $this->plugin->jetpackDisabled)) {
                            if (!isset($this->plugin->jetpackcd[strtolower($entity->getName())]) || $this->plugin->jetpackcd[strtolower($entity->getName())] <= time()) {
                                $this->plugin->flying[strtolower($entity->getName())] = isset($this->plugin->flyremaining[strtolower($entity->getName())]) ? time() + $this->plugin->flyremaining[strtolower($entity->getName())] : time() + 300;
                                $this->plugin->jetpackcd[strtolower($entity->getName())] = isset($this->plugin->flyremaining[strtolower($entity->getName())]) ? time() + (360 - (300 - $this->plugin->flyremaining[strtolower($entity->getName())])) : time() + 360;
                                $entity->sendTip(TextFormat::GREEN . "Jetpack enabled. Sneak again to turn off your jetpack.");
                                if (isset($this->plugin->flyremaining[strtolower($entity->getName())])) {
                                    unset($this->plugin->flyremaining[strtolower($entity->getName())]);
                                }
                            }
                        } else {
                            $entity->sendTip(TextFormat::RED . "Jetpacks are disabled in this world.");
                        }
                    }
                }
            }
            if ($event instanceof DataPacketReceiveEvent) {
                $packet = $event->getPacket();
                if ($packet instanceof PlayerActionPacket) {
                    $action = $packet->action;
                    switch ($action) {
                        case 8:
                            $enchantment = $this->plugin->getEnchantment($entity->getInventory()->getBoots(), CustomEnchants::SPRINGS);
                            if ($enchantment !== null) {
                                $entity->setMotion(new Vector3(0, $entity->getJumpVelocity() + 0.4));
                                $this->plugin->nofall[strtolower($entity->getName())] = time() + 1;
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * @param Block $block
     * @param Player $player
     * @param Block|null $oldblock
     */
    public function breakTree(Block $block, Player $player, Block $oldblock = null)
    {
        $item = $player->getInventory()->getItemInHand();
        for ($i = 0; $i <= 5; $i++) {
            if ($this->plugin->mined[strtolower($player->getName())] > 800) {
                break;
            }
            $this->plugin->using[strtolower($player->getName())] = time() + 1;
            $side = $block->getSide($i);
            if ($oldblock !== null) {
                if ($side->equals($oldblock)) {
                    continue;
                }
            }
            if ($side->getId() !== Block::WOOD && $side->getId() !== Block::WOOD2) {
                continue;
            }
            $player->getLevel()->useBreakOn($side, $item, $player);
            $this->plugin->mined[strtolower($player->getName())]++;
            $this->breakTree($side, $player, $block);
        }
    }

    /**
     * @return int
     */
    public function getBounty()
    {
        $random = mt_rand(0, 75);
        $currentchance = 2.5;
        if ($random < $currentchance) {
            return Item::EMERALD;
        }
        $currentchance += 5;
        if ($random < $currentchance) {
            return Item::DIAMOND;
        }
        $currentchance += 15;
        if ($random < $currentchance) {
            return Item::GOLD_INGOT;
        }
        $currentchance += 27.5;
        if ($random < $currentchance) {
            return Item::IRON_INGOT;
        }
        return Item::COAL;
    }
}
