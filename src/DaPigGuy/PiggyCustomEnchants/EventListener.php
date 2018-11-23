<?php

namespace DaPigGuy\PiggyCustomEnchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use DaPigGuy\PiggyCustomEnchants\Entities\PiggyFireball;
use DaPigGuy\PiggyCustomEnchants\Entities\PiggyWitherSkull;
use DaPigGuy\PiggyCustomEnchants\Entities\PigProjectile;
use DaPigGuy\PiggyCustomEnchants\Tasks\GoeyTask;
use DaPigGuy\PiggyCustomEnchants\Tasks\GrapplingTask;
use DaPigGuy\PiggyCustomEnchants\Tasks\HallucinationTask;
use DaPigGuy\PiggyCustomEnchants\Tasks\ImplantsTask;
use DaPigGuy\PiggyCustomEnchants\Tasks\MoltenTask;
use DaPigGuy\PiggyCustomEnchants\Tasks\PlaceTask;
use pocketmine\block\Block;
use pocketmine\block\Crops;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Axe;
use pocketmine\item\Item;
use pocketmine\item\Sword;
use pocketmine\level\particle\FlameParticle;
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
 * @package DaPigGuy\PiggyCustomEnchants
 */
class EventListener implements Listener
{
    const ORE_TIER = [
        Block::COAL_ORE => 1,
        Block::IRON_ORE => 2,
        Block::GOLD_ORE => 3,
        Block::DIAMOND_ORE => 4,
        Block::EMERALD_ORE => 5
    ];

    const SMELTED_ITEM = [
        Item::COBBLESTONE => [Item::STONE, 0],
        Item::IRON_ORE => [Item::IRON_INGOT, 0],
        Item::GOLD_ORE => [Item::GOLD_INGOT, 0],
        Item::SAND => [Item::GLASS, 0],
        Item::CLAY => [Item::BRICK, 0],
        Item::NETHERRACK => [Item::NETHER_BRICK, 0],
        Item::STONE_BRICK => [Item::STONE_BRICK, 2],
        Item::CACTUS => [Item::DYE, 2],
        Item::WOOD => [Item::COAL, 1],
        Item::WOOD2 => [Item::COAL, 1],
    ];

    /** @var Main */
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
     * @param Player $player
     * @param Event  $event
     */
    public function checkToolEnchants(Player $player, Event $event)
    {
        if ($event instanceof BlockBreakEvent) {
            $block = $event->getBlock();
            $drops = $event->getDrops();
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::EXPLOSIVE);
            if ($enchantment !== null) {
                if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                    $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
                    $explosion = new PiggyExplosion($this->plugin, $block, $enchantment->getLevel() * 5, $player);
                    $explosion->explodeA();
                    $explosion->explodeB();
                }
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::LUMBERJACK);
            if ($enchantment !== null) {
                if ($player->isSneaking()) {
                    if ($block->getId() == Block::WOOD || $block->getId() == Block::WOOD2) {
                        if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                            $this->plugin->mined[$player->getLowerCaseName()] = 0;
                            $this->breakTree($block, $player);
                        }
                    }
                }
                $event->setInstaBreak(true);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::DRILLER);
            if ($enchantment !== null) {
                if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                    if (isset($this->plugin->blockface[$player->getLowerCaseName()])) {
                        $side = $this->plugin->blockface[$player->getLowerCaseName()];
                        $sides = [$side <= 1 ? $side + 2 : $side - 2, $side > 1 && $side < 4 ? $side + 2 : ($side >= 4 ? $side - 4 : $side + 4)];
                        $item = $player->getInventory()->getItemInHand();
                        $blocks = [];
                        for ($i = 0; $i <= $enchantment->getLevel(); $i++) {
                            $b = $block->getSide($side ^ 0x01, $i);
                            $b1 = $b->getSide($sides[0]);
                            $b2 = $b->getSide($sides[0] ^ 0x01);
                            $blocks[] = $b->getSide($sides[1]);
                            $blocks[] = $b->getSide($sides[1] ^ 0x01);
                            $blocks[] = $b1;
                            $blocks[] = $b2;
                            $blocks[] = $b1->getSide($sides[1] ^ 0x01);
                            $blocks[] = $b2->getSide($sides[1] ^ 0x01);
                            $blocks[] = $b1->getSide($sides[1]);
                            $blocks[] = $b2->getSide($sides[1]);
                            if ($b !== $block) {
                                $blocks[] = $b;
                            }
                        }
                        $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
                        foreach ($blocks as $b) {
                            $block->getLevel()->useBreakOn($b, $item, $player);
                        }
                        unset($this->plugin->blockface[$player->getLowerCaseName()]);
                    }
                }
                $event->setInstaBreak(true);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::JACKPOT);
            if ($enchantment !== null) {
                $chance = 10 * $enchantment->getLevel();
                $random = mt_rand(0, 100);
                if ($random <= $chance) {
                    if (isset(self::ORE_TIER[$block->getId()])) {
                        $tier = self::ORE_TIER[$block->getId()];
                        if (($tierkey = array_search($tier + 1, self::ORE_TIER)) !== false) {
                            foreach ($drops as $key => $drop) {
                                foreach ($block->getDrops($player->getInventory()->getItemInHand()) as $originaldrop) {
                                    if ($drop->equals($originaldrop)) {
                                        unset($drops[$key]);
                                        foreach (Block::get($tierkey, $originaldrop->getDamage())->getDrops(Item::get(Item::DIAMOND_PICKAXE)) as $newdrop) { //Diamond Pickaxe to make sure the item drops
                                            $drops[] = $newdrop;
                                        }
                                        $event->setDrops($drops);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::SMELTING);
            if ($enchantment !== null) {
                $finaldrop = array();
                $otherdrops = array();
                foreach ($drops as $drop) {
                    if (isset(self::SMELTED_ITEM[$drop->getId()])) {
                        $finaldrop[] = Item::get(self::SMELTED_ITEM[$drop->getId()][0], self::SMELTED_ITEM[$drop->getId()][1], $drop->getCount());
                        continue;
                    }
                    if ($drop->getId() == Item::SPONGE && $drop->getDamage() == 1) {
                        $finaldrop[] = Item::get(Item::SPONGE, 0, $drop->getCount());
                        continue;
                    }
                    $finaldrop[] = $drop;

                }
                $event->setDrops($drops = array_merge($finaldrop, $otherdrops));
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::ENERGIZING);
            if ($enchantment !== null && $player->hasEffect(Effect::HASTE) !== true) {
                $effect = new EffectInstance(Effect::getEffect(Effect::HASTE), 20, 1 + $enchantment->getLevel() - 2, false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::QUICKENING);
            if ($enchantment !== null && $player->hasEffect(Effect::SPEED) !== true) {
                $effect = new EffectInstance(Effect::getEffect(Effect::SPEED), 40, 3 + $enchantment->getLevel() - 2, false);
                $player->addEffect($effect);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::TELEPATHY);
            if ($enchantment !== null) {
                foreach ($drops as $drop) {
                    $player->getInventory()->addItem($drop);
                }
                $event->setDrops([]);
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::FARMER);
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
                    if ($player->getInventory()->contains($seed)) {
                        $pos = $block->subtract(0, 1);
                        $this->plugin->getScheduler()->scheduleDelayedTask(new PlaceTask($this->plugin, $pos, $block->getLevel(), $seed, $player), 1);
                    }
                }
            }
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::HARVEST);
            if ($enchantment !== null) {
                $radius = $enchantment->getLevel();
                if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                    if ($block instanceof Crops) {
                        for ($x = -$radius; $x <= $radius; $x++) {
                            for ($z = -$radius; $z <= $radius; $z++) {
                                $pos = $block->add($x, 0, $z);
                                if ($block->getLevel()->getBlock($pos) instanceof Crops) {
                                    $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
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
            $enchantment = $player->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::FERTILIZER);
            if ($enchantment !== null) {
                if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                    if ($this->plugin->checkBlocks($block, [Block::DIRT, Block::GRASS])) {
                        $radius = $enchantment->getLevel();
                        for ($x = -$radius; $x <= $radius; $x++) {
                            for ($z = -$radius; $z <= $radius; $z++) {
                                $pos = $block->add($x, 0, $z);
                                if ($this->plugin->checkBlocks(Position::fromObject($pos, $block->getLevel()), [Block::DIRT, Block::GRASS])) {
                                    $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
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
     * @param Block      $block
     * @param Player     $player
     * @param Block|null $oldblock
     */
    public function breakTree(Block $block, Player $player, Block $oldblock = null)
    {
        $item = $player->getInventory()->getItemInHand();
        for ($i = 0; $i <= 5; $i++) {
            if ($this->plugin->mined[$player->getLowerCaseName()] > 800) {
                break;
            }
            $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
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
            $this->plugin->mined[$player->getLowerCaseName()]++;
            $this->breakTree($side, $player, $block);
        }
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
     * @param Entity            $entity
     * @param EntityEvent|Event $event
     */
    public function checkArmorEnchants(Entity $entity, Event $event)
    {
        if ($entity instanceof Player) {
            $random = new Random();
            if ($event instanceof EntityDamageEvent) {
                $cause = $event->getCause();
                $antikb = 4;
                if ($cause == EntityDamageEvent::CAUSE_FALL) {
                    $enchantment = $entity->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::STOMP);
                    if ($enchantment !== null) {
                        $entities = $entity->getLevel()->getNearbyEntities($entity->getBoundingBox());
                        foreach ($entities as $e) {
                            if ($entity === $e) {
                                continue;
                            }
                            $ev = new EntityDamageByEntityEvent($entity, $e, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $event->getFinalDamage() / 2);
                            $ev->call();
                            $e->attack($ev);
                        }
                        if (count($entities) > 1) {
                            $event->setModifier(-($event->getFinalDamage() * (3 / 4)), CustomEnchantsIds::STOMP);
                        }
                    }
                }
                foreach ($entity->getArmorInventory()->getContents() as $slot => $armor) {
                    $enchantment = $armor->getEnchantment(CustomEnchantsIds::REVIVE);
                    if ($enchantment !== null) {
                        if ($event->getFinalDamage() >= $entity->getHealth()) {
                            if ($enchantment->getLevel() > 1) {
                                $entity->getArmorInventory()->setItem($slot, $this->plugin->addEnchantment($armor, $enchantment->getId(), $enchantment->getLevel() - 1));
                            } else {
                                $entity->getArmorInventory()->setItem($slot, $this->plugin->removeEnchantment($armor, $enchantment));
                            }
                            $entity->removeAllEffects();
                            $entity->setHealth($entity->getMaxHealth());
                            $entity->setFood($entity->getMaxFood());
                            $entity->setXpLevel(0);
                            $entity->setXpProgress(0);
                            $effect = new EffectInstance(Effect::getEffect(Effect::NAUSEA), 600, 0, false);
                            $entity->addEffect($effect);
                            $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 600, 0, false);
                            $entity->addEffect($effect);
                            for ($i = $entity->y; $i <= 256; $i += 0.25) {
                                $entity->getLevel()->addParticle(new FlameParticle(new Vector3($entity->x, $i, $entity->z)));
                            }
                            $entity->sendTip(TextFormat::GREEN . "You were revived.");
                            foreach ($event->getModifiers() as $modifier => $damage) {
                                $event->setModifier(0, $modifier);
                            }
                            $event->setBaseDamage(0);
                        }
                    }
                    $enchantment = $armor->getEnchantment(CustomEnchantsIds::SELFDESTRUCT);
                    if ($enchantment !== null) {
                        if ($event->getFinalDamage() >= $entity->getHealth()) { //Compatibility for plugins that auto respawn players on death
                            for ($i = $enchantment->getLevel(); $i >= 0; $i--) {
                                $tnt = Entity::createEntity("PrimedTNT", $entity->getLevel(), new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $entity->x), new DoubleTag("", $entity->y), new DoubleTag("", $entity->z)]), "Motion" => new ListTag("Motion", [new DoubleTag("", $random->nextFloat() * 1.5 - 1), new DoubleTag("", $random->nextFloat() * 1.5), new DoubleTag("", $random->nextFloat() * 1.5 - 1)]), "Rotation" => new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]), "Fuse" => new ByteTag("Fuse", 40)]));
                                $tnt->spawnToAll();
                            }
                        }
                    }
                    $enchantment = $armor->getEnchantment(CustomEnchantsIds::ENDERSHIFT);
                    if ($enchantment !== null) {
                        if ($entity->getHealth() - $event->getFinalDamage() <= 4) {
                            if (!isset($this->plugin->endershiftcd[$entity->getLowerCaseName()]) || time() > $this->plugin->endershiftcd[$entity->getLowerCaseName()]) {
                                $this->plugin->endershiftcd[$entity->getLowerCaseName()] = time() + 300;
                                if (!$entity->hasEffect(Effect::SPEED)) {
                                    $effect = new EffectInstance(Effect::getEffect(Effect::SPEED), 200 * $enchantment->getLevel(), $enchantment->getLevel() + 3, false);
                                    $entity->addEffect($effect);
                                }
                                if (!$entity->hasEffect(Effect::ABSORPTION)) {
                                    $effect = new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 200 * $enchantment->getLevel(), $enchantment->getLevel() + 3, false);
                                    $entity->addEffect($effect);
                                }
                                $entity->sendMessage("You feel a rush of energy coming from your armor!");
                            }
                        }
                    }
                    $enchantment = $armor->getEnchantment(CustomEnchantsIds::BERSERKER);
                    if ($enchantment !== null) {
                        if ($entity->getHealth() - $event->getFinalDamage() <= 4) {
                            if ((!isset($this->plugin->berserkercd[$entity->getLowerCaseName()]) || time() > $this->plugin->berserkercd[$entity->getLowerCaseName()]) && $entity->hasEffect(Effect::STRENGTH) !== true) {
                                $this->plugin->berserkercd[$entity->getLowerCaseName()] = time() + 300;
                                $effect = new EffectInstance(Effect::getEffect(Effect::STRENGTH), 200 * $enchantment->getLevel(), 3 + $enchantment->getLevel(), false);
                                $entity->addEffect($effect);
                                $entity->sendMessage("Your bloodloss makes your stronger!");
                            }
                        }
                    }
                }
                if ($event instanceof EntityDamageByEntityEvent) {
                    $damager = $event->getDamager();
                    foreach ($entity->getArmorInventory()->getContents() as $slot => $armor) {
                        $enchantment = $armor->getEnchantment(CustomEnchantsIds::MOLTEN);
                        if ($enchantment !== null) {
                            $this->plugin->getScheduler()->scheduleDelayedTask(new MoltenTask($this->plugin, $damager, $enchantment->getLevel()), 1);
                        }
                        $enchantment = $armor->getEnchantment(CustomEnchantsIds::ENLIGHTED);
                        if ($enchantment !== null && $entity->hasEffect(Effect::REGENERATION) !== true) {
                            $effect = new EffectInstance(Effect::getEffect(Effect::REGENERATION), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                            $entity->addEffect($effect);
                        }
                        if ($damager instanceof Living) {
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::HARDENED);
                            if ($enchantment !== null && $damager->hasEffect(Effect::WEAKNESS) !== true) {
                                $effect = new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                                $damager->addEffect($effect);
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::POISONED);
                            if ($enchantment !== null && $damager->hasEffect(Effect::POISON) !== true) {
                                $effect = new EffectInstance(Effect::getEffect(Effect::POISON), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                                $damager->addEffect($effect);
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::FROZEN);
                            if ($enchantment !== null && $damager->hasEffect(Effect::SLOWNESS) !== true) {
                                $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                                $damager->addEffect($effect);
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::REVULSION);
                            if ($enchantment !== null && $damager->hasEffect(Effect::NAUSEA) !== true) {
                                $effect = new EffectInstance(Effect::getEffect(Effect::NAUSEA), 20 * $enchantment->getLevel(), 0, false);
                                $damager->addEffect($effect);
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::CURSED);
                            if ($enchantment !== null && $damager->hasEffect(Effect::WITHER) !== true) {
                                $effect = new EffectInstance(Effect::getEffect(Effect::WITHER), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                                $damager->addEffect($effect);
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::DRUNK);
                            if ($enchantment !== null) {
                                if (!$damager->hasEffect(Effect::SLOWNESS)) {
                                    $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                                    $damager->addEffect($effect);
                                }
                                if (!$damager->hasEffect(Effect::MINING_FATIGUE)) {
                                    $effect = new EffectInstance(Effect::getEffect(Effect::MINING_FATIGUE), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                                    $damager->addEffect($effect);
                                }
                                if (!$damager->hasEffect(Effect::NAUSEA)) {
                                    $effect = new EffectInstance(Effect::getEffect(Effect::NAUSEA), 60 * $enchantment->getLevel(), 0, false);
                                    $damager->addEffect($effect);
                                }
                            }
                        }
                        $enchantment = $armor->getEnchantment(CustomEnchantsIds::CLOAKING);
                        if ($enchantment !== null) {
                            if ((!isset($this->plugin->cloakingcd[$entity->getLowerCaseName()]) || time() > $this->plugin->cloakingcd[$entity->getLowerCaseName()]) && $entity->hasEffect(Effect::INVISIBILITY)) {
                                $this->plugin->cloakingcd[$entity->getLowerCaseName()] = time() + 10;
                                $effect = new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 60 * $enchantment->getLevel(), 0, false);
                                $entity->addEffect($effect);
                                $entity->sendMessage(TextFormat::DARK_GRAY . "You have become invisible!");
                            }
                        }
                        $enchantment = $armor->getEnchantment(CustomEnchantsIds::ANTIKNOCKBACK);
                        if ($enchantment !== null) {
                            $event->setKnockBack($event->getKnockBack() - ($event->getKnockBack() / $antikb));
                            $antikb--;
                        }
                        if ($damager instanceof Player) {
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::ARMORED);
                            if ($enchantment !== null) {
                                if ($damager->getInventory()->getItemInHand() instanceof Sword) {
                                    $event->setModifier(-($event->getFinalDamage() * 0.2 * $enchantment->getLevel()), CustomEnchantsIds::ARMORED);
                                }
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::TANK);
                            if ($enchantment !== null) {
                                if ($damager->getInventory()->getItemInHand() instanceof Axe) {
                                    $event->setModifier(-($event->getFinalDamage() * 0.2 * $enchantment->getLevel()), CustomEnchantsIds::TANK);
                                }
                            }
                            $enchantment = $armor->getEnchantment(CustomEnchantsIds::HEAVY);
                            if ($enchantment !== null) {
                                if ($damager->getInventory()->getItemInHand()->getId() == Item::BOW) {
                                    $event->setModifier(-($event->getFinalDamage() * 0.2 * $enchantment->getLevel()), CustomEnchantsIds::HEAVY);
                                }
                            }
                        }
                    }
                }
            }
            if ($event instanceof EntityEffectAddEvent) {
                $effect = $event->getEffect();
                $enchantment = $entity->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::FOCUSED);
                if ($enchantment !== null) {
                    if (!isset($this->plugin->using[$entity->getLowerCaseName()]) || $this->plugin->using[$entity->getLowerCaseName()] < time()) {
                        if ($effect->getId() == Effect::NAUSEA) {
                            if ($effect->getEffectLevel() - ($enchantment->getLevel() * 2) <= 0) {
                                $event->setCancelled();
                            } else {
                                $event->setCancelled();
                                $this->plugin->using[$entity->getLowerCaseName()] = time() + 1;
                                $entity->addEffect($effect->setAmplifier($effect->getEffectLevel() - (1 + ($enchantment->getLevel() * 2))));
                            }
                        }
                    }
                }
                $enchantment = $entity->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::ANTITOXIN);
                if ($enchantment !== null) {
                    if ($effect->getId() == Effect::POISON) {
                        $event->setCancelled();
                    }
                }
            }
            if ($event instanceof PlayerMoveEvent) {
                $enchantment = $entity->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::MAGMAWALKER);
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
                $enchantment = $entity->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::MEDITATION);
                if ($enchantment !== null) {
                    if ($event->getFrom()->floor() !== $event->getTo()->floor()) {
                        $this->plugin->meditationTick[$entity->getLowerCaseName()] = 0;
                    }
                }
                $enchantment = $entity->getArmorInventory()->getHelmet()->getEnchantment(CustomEnchantsIds::IMPLANTS);
                if ($enchantment !== null) {
                    if ($event->getFrom()->floor() !== $event->getTo()->floor()) {
                        if (!isset($this->plugin->implantscd[$entity->getLowerCaseName()]) || $this->plugin->implantscd[$entity->getLowerCaseName()] < time()) {
                            if ($entity->getFood() < 20) {
                                $entity->setFood($entity->getFood() + $enchantment->getLevel() > 20 ? 20 : $entity->getFood() + $enchantment->getLevel());
                            }
                            if ($entity->getAirSupplyTicks() < $entity->getMaxAirSupplyTicks() && isset($this->plugin->implants[$entity->getLowerCaseName()]) !== true) {
                                $this->plugin->implants[$entity->getLowerCaseName()] = true;
                                $task = new ImplantsTask($this->plugin, $entity);
                                $handler = $this->plugin->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 60);
                                $task->setHandler($handler);
                            }
                            $this->plugin->implantscd[$entity->getLowerCaseName()] = time() + 1;
                        }
                    }
                }
            }
            if ($event instanceof PlayerToggleSneakEvent) {
                $shrinkpoints = 0;
                $growpoints = 0;
                $shrinklevel = 0;
                $growlevel = 0;
                foreach ($entity->getArmorInventory()->getContents() as $armor) {
                    $enchantment = $armor->getEnchantment(CustomEnchantsIds::SHRINK);
                    if ($enchantment !== null) {
                        $shrinklevel += $enchantment->getLevel();
                        $shrinkpoints++;
                    }
                    $enchantment = $armor->getEnchantment(CustomEnchantsIds::GROW);
                    if ($enchantment !== null) {
                        $growlevel += $enchantment->getLevel();
                        $growpoints++;
                    }
                }
                if ($shrinkpoints >= 4) {
                    if (isset($this->plugin->shrunk[$entity->getLowerCaseName()]) && $this->plugin->shrunk[$entity->getLowerCaseName()] > time()) {
                        $this->plugin->shrinkremaining[$entity->getLowerCaseName()] = $this->plugin->shrunk[$entity->getLowerCaseName()] - time();
                        unset($this->plugin->shrinkcd[$entity->getLowerCaseName()]);
                        unset($this->plugin->shrunk[$entity->getLowerCaseName()]);
                        $entity->setScale(1);
                        $entity->sendTip(TextFormat::RED . "You have grown back to normal size.");
                    } else {
                        if (!isset($this->plugin->shrinkcd[$entity->getLowerCaseName()]) || $this->plugin->shrinkcd[$entity->getLowerCaseName()] <= time()) {
                            $scale = $entity->getScale() - 0.70 - (($shrinklevel / 4) * 0.05);
                            $entity->setScale($scale);
                            $this->plugin->shrunk[$entity->getLowerCaseName()] = isset($this->plugin->shrinkremaining[$entity->getLowerCaseName()]) ? time() + $this->plugin->shrinkremaining[$entity->getLowerCaseName()] : time() + 60;
                            $this->plugin->shrinkcd[$entity->getLowerCaseName()] = isset($this->plugin->shrinkremaining[$entity->getLowerCaseName()]) ? time() + (75 - (60 - $this->plugin->shrinkremaining[$entity->getLowerCaseName()])) : time() + 75;
                            $entity->sendTip(TextFormat::GREEN . "You have shrunk. Sneak again to grow back to normal size.");
                            if (isset($this->plugin->shrinkremaining[$entity->getLowerCaseName()])) {
                                unset($this->plugin->shrinkremaining[$entity->getLowerCaseName()]);
                            }
                        }
                    }
                }
                if ($growpoints >= 4) {
                    if (isset($this->plugin->grew[$entity->getLowerCaseName()]) && $this->plugin->grew[$entity->getLowerCaseName()] > time()) {
                        $this->plugin->growremaining[$entity->getLowerCaseName()] = $this->plugin->grew[$entity->getLowerCaseName()] - time();
                        unset($this->plugin->growcd[$entity->getLowerCaseName()]);
                        unset($this->plugin->grew[$entity->getLowerCaseName()]);
                        $entity->setScale(1);
                        $entity->sendTip(TextFormat::RED . "You have shrunk back to normal size.");
                    } else {
                        if (!isset($this->plugin->growcd[$entity->getLowerCaseName()]) || $this->plugin->growcd[$entity->getLowerCaseName()] <= time()) {
                            $scale = $entity->getScale() + 0.30 + (($growlevel / 4) * 0.05);
                            $entity->setScale($scale);
                            $this->plugin->grew[$entity->getLowerCaseName()] = isset($this->plugin->growremaining[$entity->getLowerCaseName()]) ? time() + $this->plugin->growremaining[$entity->getLowerCaseName()] : time() + 60;
                            $this->plugin->growcd[$entity->getLowerCaseName()] = isset($this->plugin->growremaining[$entity->getLowerCaseName()]) ? time() + (75 - (60 - $this->plugin->growremaining[$entity->getLowerCaseName()])) : time() + 75;
                            $entity->sendTip(TextFormat::GREEN . "You have grown. Sneak again to shrink back to normal size.");
                            if (isset($this->plugin->growremaining[$entity->getLowerCaseName()])) {
                                unset($this->plugin->growremaining[$entity->getLowerCaseName()]);
                            }
                        }
                    }
                }
                $enchantment = $entity->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::JETPACK);
                if ($enchantment !== null) {
                    if (isset($this->plugin->flying[$entity->getLowerCaseName()]) && $this->plugin->flying[$entity->getLowerCaseName()] > time()) {
                        if ($entity->isOnGround()) {
                            $this->plugin->flyremaining[$entity->getLowerCaseName()] = $this->plugin->flying[$entity->getLowerCaseName()] - time();
                            unset($this->plugin->jetpackcd[$entity->getLowerCaseName()]);
                            unset($this->plugin->flying[$entity->getLowerCaseName()]);
                            $entity->sendTip(TextFormat::RED . "Jetpack disabled.");
                        } else {
                            $entity->sendTip(TextFormat::RED . "It is unsafe to disable your jetpack in the air.");
                        }
                    } else {
                        if (!in_array($event->getPlayer()->getLevel()->getName(), $this->plugin->jetpackDisabled)) {
                            if (!isset($this->plugin->jetpackcd[$entity->getLowerCaseName()]) || $this->plugin->jetpackcd[$entity->getLowerCaseName()] <= time()) {
                                $this->plugin->flying[$entity->getLowerCaseName()] = isset($this->plugin->flyremaining[$entity->getLowerCaseName()]) ? time() + $this->plugin->flyremaining[$entity->getLowerCaseName()] : time() + 300;
                                $this->plugin->jetpackcd[$entity->getLowerCaseName()] = isset($this->plugin->flyremaining[$entity->getLowerCaseName()]) ? time() + (360 - (300 - $this->plugin->flyremaining[$entity->getLowerCaseName()])) : time() + 360;
                                $entity->sendTip(TextFormat::GREEN . "Jetpack enabled. Sneak again to turn off your jetpack.");
                                if (isset($this->plugin->flyremaining[$entity->getLowerCaseName()])) {
                                    unset($this->plugin->flyremaining[$entity->getLowerCaseName()]);
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
                            $enchantment = $entity->getArmorInventory()->getBoots()->getEnchantment(CustomEnchantsIds::SPRINGS);
                            if ($enchantment !== null) {
                                $entity->setMotion(new Vector3(0, $entity->getJumpVelocity() + 0.4));
                                $this->plugin->nofall[$entity->getLowerCaseName()] = time() + 1;
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        $cause = $event->getCause();
        if ($cause == EntityDamageEvent::CAUSE_FALL && $entity instanceof Player && (isset($this->plugin->nofall[$entity->getLowerCaseName()]) || isset($this->plugin->flying[$entity->getLowerCaseName()]))) {
            unset($this->plugin->nofall[$entity->getLowerCaseName()]);
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
                if ($damager->getInventory()->getItemInHand()->getId() !== Item::BOW) { //TODO: Move to canUse() function
                    $this->checkGlobalEnchants($damager, $entity, $event);
                }
            }
        }
        $this->checkArmorEnchants($entity, $event);
    }

    /**
     * @param Player            $damager
     * @param Entity            $entity
     * @param EntityEvent|Event $event
     */
    public function checkGlobalEnchants(Player $damager, Entity $entity = null, Event $event)
    {
        //TODO: Check to make sure you can use enchant with item
        if ($event instanceof EntityDamageEvent) {
            if ($entity instanceof Living) {
                $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::BLIND);
                if ($enchantment !== null && $entity->hasEffect(Effect::BLINDNESS) !== true) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 100 + 20 * $enchantment->getLevel(), 0, false);
                    $entity->addEffect($effect);
                }
                $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::POISON);
                if ($enchantment !== null && $entity->hasEffect(Effect::POISON) !== true) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::POISON), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                    $entity->addEffect($effect);
                }
                $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::CRIPPLINGSTRIKE);
                if ($enchantment !== null) {
                    if (!$entity->hasEffect(Effect::NAUSEA)) {
                        $effect = new EffectInstance(Effect::getEffect(Effect::NAUSEA), 100 * $enchantment->getLevel(), 0, false);
                        $entity->addEffect($effect);
                    }
                    if (!$entity->hasEffect(Effect::SLOWNESS)) {
                        $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 100 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                        $entity->addEffect($effect);
                    }
                }
                $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::WITHER);
                if ($enchantment !== null && $entity->hasEffect(Effect::WITHER) !== true) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::WITHER), 60 * $enchantment->getLevel(), $enchantment->getLevel(), false);
                    $entity->addEffect($effect);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::LIFESTEAL);
            if ($enchantment !== null) {
                if ($damager->getHealth() + 2 + $enchantment->getLevel() <= $damager->getMaxHealth()) {
                    $damager->setHealth($damager->getHealth() + 2 + $enchantment->getLevel());
                } else {
                    $damager->setHealth($damager->getMaxHealth());
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::DEATHBRINGER);
            if ($enchantment !== null) {
                $damage = 2 + ($enchantment->getLevel() / 10);
                $event->setModifier($damage, CustomEnchantsIds::DEATHBRINGER);
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::GOOEY);
            if ($enchantment !== null) {
                $task = new GoeyTask($this->plugin, $entity, $enchantment->getLevel());
                $this->plugin->getScheduler()->scheduleDelayedTask($task, 1);
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::VAMPIRE);
            if ($enchantment !== null) {
                if (!isset($this->plugin->vampirecd[$damager->getLowerCaseName()]) || time() > $this->plugin->vampirecd[$damager->getLowerCaseName()]) {
                    $this->plugin->vampirecd[$damager->getLowerCaseName()] = time() + 5;
                    if ($damager->getHealth() + ($event->getFinalDamage() / 2) <= $damager->getMaxHealth()) {
                        $damager->setHealth($damager->getHealth() + ($event->getFinalDamage() / 2));
                    } else {
                        $damager->setHealth($damager->getMaxHealth());
                    }
                    if ($damager->getFood() + ($event->getFinalDamage() / 2) <= $damager->getMaxFood()) {
                        $damager->setFood($damager->getFood() + ($event->getFinalDamage() / 2));
                    } else {
                        $damager->setFood($damager->getMaxFood());
                    }
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::CHARGE);
            if ($enchantment !== null) {
                if ($damager->isSprinting()) {
                    $event->setModifier((($event->getFinalDamage() * (1 + 0.10 * $enchantment->getLevel())) - $event->getFinalDamage()), CustomEnchantsIds::CHARGE);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::AERIAL);
            if ($enchantment !== null) {
                if (!$damager->isOnGround()) {
                    $event->setModifier((($event->getFinalDamage() * (1 + 0.10 * $enchantment->getLevel())) - $event->getFinalDamage()), CustomEnchantsIds::AERIAL);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::DISARMING);
            if ($enchantment !== null) {
                if ($entity instanceof Player) {
                    $chance = 10 * $enchantment->getLevel();
                    $random = mt_rand(0, 100);
                    if ($random <= $chance) {
                        $item = $entity->getInventory()->getItemInHand();
                        $entity->getInventory()->removeItem($item);
                        $entity->dropItem($item);
                    }
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::HALLUCINATION);
            if ($enchantment !== null) {
                $chance = 5 * $enchantment->getLevel();
                $random = mt_rand(0, 100);
                if ($random <= $chance && $entity instanceof Player && isset($this->plugin->hallucination[$entity->getLowerCaseName()]) !== true) {
                    $this->plugin->hallucination[$entity->getLowerCaseName()] = true;
                    $task = new HallucinationTask($this->plugin, $entity, $entity->getPosition());
                    $handler = $this->plugin->getScheduler()->scheduleRepeatingTask($task, 1);
                    $task->setHandler($handler);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::BLESSED);
            if ($enchantment !== null) {
                $chance = 15 * $enchantment->getLevel();
                $random = mt_rand(0, 100);
                if ($random <= $chance) {
                    foreach ($damager->getEffects() as $effect) {
                        if ($effect->getType()->isBad()) {
                            $damager->removeEffect($effect->getId());
                        }
                    }
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::DISARMOR);
            if ($enchantment !== null) {
                if ($entity instanceof Player) {
                    $chance = 10 * $enchantment->getLevel();
                    $random = mt_rand(0, 100);
                    if ($random <= $chance) {
                        if (count($armor = $entity->getArmorInventory()->getContents(false)) > 0) {
                            $item = $armor[array_rand($armor)];
                            $entity->getArmorInventory()->removeItem($item);
                            $entity->dropItem($item);
                        }
                    }
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::BACKSTAB);
            if ($enchantment !== null) {
                if ($damager->getDirectionVector()->dot($entity->getDirectionVector()) > 0) {
                    $event->setModifier((($event->getFinalDamage() * (1 + 0.10 * $enchantment->getLevel())) - $event->getFinalDamage()), CustomEnchantsIds::BACKSTAB);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::LIGHTNING);
            if ($enchantment !== null) {
                $chance = 10 * $enchantment->getLevel();
                $random = mt_rand(0, 100);
                if ($random <= $chance) {
                    $lightning = Entity::createEntity("PiggyLightning", $entity->getLevel(), Entity::createBaseNBT($entity));
                    $lightning->setOwningEntity($damager);
                    $lightning->spawnToAll();
                }
            }
        }
        if ($event instanceof PlayerDeathEvent) {
            $drops = $event->getDrops();
            $soulbounded = [];
            $soulboundedarmor = [];
            foreach ($damager->getInventory()->getContents() as $k => $item) {
                $enchantment = $item->getEnchantment(CustomEnchantsIds::SOULBOUND);
                if ($enchantment !== null) {
                    $index = array_search($item, $drops);
                    if ($index !== false) {
                        unset($drops[$index]);
                    }
                    $soulbounded[$k] = $enchantment->getLevel() > 1 ? $this->plugin->addEnchantment($item, $enchantment->getId(), $enchantment->getLevel() - 1) : $this->plugin->removeEnchantment($item, $enchantment);
                }
            }
            foreach ($damager->getArmorInventory()->getContents() as $k => $item) {
                $enchantment = $item->getEnchantment(CustomEnchantsIds::SOULBOUND);
                if ($enchantment !== null) {
                    $index = array_search($item, $drops);
                    if ($index !== false) {
                        unset($drops[$index]);
                    }
                    $soulboundedarmor[$k] = $enchantment->getLevel() > 1 ? $this->plugin->addEnchantment($item, $enchantment->getId(), $enchantment->getLevel() - 1) : $this->plugin->removeEnchantment($item, $enchantment);
                }
            }
            $event->setDrops([]);
            $event->setKeepInventory(true);
            foreach ($drops as $drop) {
                $damager->getLevel()->dropItem($damager, $drop);
            }
            $damager->getArmorInventory()->setContents($soulboundedarmor);
            $damager->getInventory()->setContents($soulbounded);
        }
        if ($event instanceof PlayerMoveEvent) {
            foreach ($damager->getInventory()->getContents() as $slot => $item) {
                $enchantment = $item->getEnchantment(CustomEnchantsIds::AUTOREPAIR);
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
            foreach ($damager->getArmorInventory()->getContents() as $slot => $item) {
                $enchantment = $item->getEnchantment(CustomEnchantsIds::AUTOREPAIR);
                if ($enchantment !== null) {
                    $newDir = $item->getDamage() - (1 + (1 * $enchantment->getLevel()));
                    if ($newDir < 0) {
                        $item->setDamage(0);
                    } else {
                        $item->setDamage($newDir);
                    }
                    $damager->getArmorInventory()->setItem($slot, $item);
                }
            }
        }
    }

    /**
     * @param Player      $damager
     * @param Entity      $entity
     * @param EntityEvent $event
     */
    public function checkBowEnchants(Player $damager, Entity $entity, EntityEvent $event)
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::MOLOTOV);
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
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::PARALYZE);
            if ($enchantment !== null and $entity instanceof Living) {
                if (!$entity->hasEffect(Effect::SLOWNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 60 + ($enchantment->getLevel() - 1) * 20, 5 + $enchantment->getLevel() - 1, false);
                    $entity->addEffect($effect);
                }
                if (!$entity->hasEffect(Effect::BLINDNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 60 + ($enchantment->getLevel() - 1) * 20, 1, false);
                    $entity->addEffect($effect);
                }
                if (!$entity->hasEffect(Effect::WEAKNESS)) {
                    $effect = new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 60 + ($enchantment->getLevel() - 1) * 20, 5 + $enchantment->getLevel() - 1, false);
                    $entity->addEffect($effect);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::PIERCING);
            if ($enchantment !== null) {
                $event->setModifier(0, EntityDamageEvent::MODIFIER_ARMOR);
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::SHUFFLE);
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
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::BOUNTYHUNTER);
            if ($enchantment !== null) {
                if (!isset($this->plugin->bountyhuntercd[$damager->getLowerCaseName()]) || time() > $this->plugin->bountyhuntercd[$damager->getLowerCaseName()]) {
                    $bountydrop = $this->getBounty();
                    $damager->getInventory()->addItem(Item::get($bountydrop, 0, mt_rand(0, 8 + $enchantment->getLevel()) + 1));
                    $this->plugin->bountyhuntercd[$damager->getLowerCaseName()] = time() + 30;
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::HEALING);
            if ($enchantment !== null) {
                if ($entity->getHealth() + $event->getFinalDamage() + $enchantment->getLevel() <= $entity->getMaxHealth()) {
                    $entity->setHealth($entity->getHealth() + $event->getFinalDamage() + $enchantment->getLevel());
                } else {
                    $entity->setHealth($entity->getMaxHealth());
                }
                foreach ($event->getModifiers() as $modifier => $damage) {
                    $event->setModifier(0, $modifier);
                }
                $event->setBaseDamage(0);
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::HEADHUNTER);
            if ($enchantment !== null) {
                $projectile = $event->getChild();
                if ($projectile->y > $entity->getPosition()->y + $entity->getEyeHeight()) {
                    $event->setModifier((($event->getFinalDamage() * (1 + 0.10 * $enchantment->getLevel())) - $event->getFinalDamage()), CustomEnchantsIds::HEADHUNTER);
                }
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::GRAPPLING);
            if ($enchantment !== null) {
                $task = new GrapplingTask($this->plugin, $damager->getPosition(), $entity);
                $this->plugin->getScheduler()->scheduleDelayedTask($task, 1); //Delayed due to knockback interfering
            }
        }
        if ($event instanceof EntityShootBowEvent) {
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::PLACEHOLDER);
            if ($enchantment !== null) {
                $nbt = Entity::createBaseNBT($entity, $damager->getDirectionVector(), $entity->yaw, $entity->pitch);
                $newentity = Entity::createEntity("VolleyArrow", $damager->getLevel(), $nbt, $damager, $entity->isCritical(), true, false);
                $newentity->setMotion($newentity->getMotion()->multiply($event->getForce()));
                $newentity->spawnToAll();
                $entity->close();
                $entity = $newentity;
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::BLAZE);
            if ($enchantment !== null && $entity instanceof PiggyFireball !== true) {
                $nbt = Entity::createBaseNBT($entity, $damager->getDirectionVector(), $entity->yaw, $entity->pitch);
                $fireball = Entity::createEntity("PiggyFireball", $damager->getLevel(), $nbt, $damager, isset($entity->placeholder) ? $entity->placeholder : false);
                $fireball->setMotion($fireball->getMotion()->multiply($event->getForce()));
                $fireball->spawnToAll();
                $entity->close();
                $entity = $fireball;
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::PORKIFIED);
            if ($enchantment !== null && $entity instanceof PigProjectile !== true) {
                $nbt = Entity::createBaseNBT($entity, $damager->getDirectionVector(), $entity->yaw, $entity->pitch);
                $pig = Entity::createEntity("PigProjectile", $damager->getLevel(), $nbt, $damager, isset($entity->placeholder) ? $entity->placeholder : false, $enchantment->getLevel());
                $pig->setMotion($pig->getMotion()->multiply($event->getForce()));
                $pig->spawnToAll();
                $entity->close();
                $entity = $pig;
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::WITHERSKULL);
            if ($enchantment !== null && $entity instanceof PiggyWitherSkull !== true) {
                $nbt = Entity::createBaseNBT($entity, $damager->getDirectionVector(), $entity->yaw, $entity->pitch);
                $skull = Entity::createEntity("PiggyWitherSkull", $damager->getLevel(), $nbt, $damager, isset($entity->placeholder) ? $entity->placeholder : false, $enchantment->getLevel() > 1 ? true : false);
                $skull->setMotion($skull->getMotion()->multiply($event->getForce()));
                $skull->spawnToAll();
                $entity->close();
                $entity = $skull;
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::VOLLEY);
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
                        $projectile = Entity::createEntity("VolleyArrow", $damager->getLevel(), $nbt, $damager, $entity->isCritical(), false, true);
                    }
                    if ($entity instanceof PiggyFireball) {
                        $nbt = Entity::createBaseNBT($damager->add(0, $damager->getEyeHeight()), $damager->getDirectionVector(), $damager->yaw, $damager->pitch);
                        $projectile = Entity::createEntity("PiggyFireball", $damager->getLevel(), $nbt, $damager);
                    }
                    if ($entity instanceof PigProjectile) {
                        $nbt = Entity::createBaseNBT($damager->add(0, $damager->getEyeHeight()), $damager->getDirectionVector(), $damager->yaw, $damager->pitch);
                        $projectile = Entity::createEntity("PigProjectile", $damager->getLevel(), $nbt, $damager, false, $entity->getPorkLevel());
                    }
                    if ($entity instanceof PiggyWitherSkull) {
                        $nbt = Entity::createBaseNBT($damager->add(0, $damager->getEyeHeight()), $damager->getDirectionVector(), $damager->yaw, $damager->pitch);
                        $projectile = Entity::createEntity("PiggyWitherSkull", $damager->getLevel(), $nbt, $damager);
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
        if ($event instanceof ProjectileHitBlockEvent && $entity instanceof Projectile) {
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::GRAPPLING);
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
                $this->plugin->nofall[$damager->getLowerCaseName()] = time() + 1;
            }
            $enchantment = $damager->getInventory()->getItemInHand()->getEnchantment(CustomEnchantsIds::MISSILE);
            if ($enchantment !== null) {
                for ($i = 0; $i <= $enchantment->getLevel(); $i++) {
                    $tnt = Entity::createEntity("PrimedTNT", $entity->getLevel(), new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $entity->x), new DoubleTag("", $entity->y), new DoubleTag("", $entity->z)]), "Motion" => new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]), "Rotation" => new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]), "Fuse" => new ByteTag("Fuse", 40)]));
                    $tnt->spawnToAll();
                    $entity->flagForDespawn();
                }
            }
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

    /**
     * @param EntityEffectAddEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onEffect(EntityEffectAddEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $this->checkArmorEnchants($entity, $event);
        }
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

    /**
     * @param InventoryTransactionEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onTransaction(InventoryTransactionEvent $event)
    {
        $transaction = $event->getTransaction();

        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $item_clicked = $action->getSourceItem();
                if ($item_clicked->getId() === Item::ENCHANTED_BOOK) {
                    $enchantedbook_action = $action;
                } elseif (!$item_clicked->isNull()) {
                    $equipment_action = $action;
                }
            }
        }

        if (isset($enchantedbook_action, $equipment_action)) {
            $book = $enchantedbook_action->getSourceItem();
            $equipment = $equipment_action->getSourceItem();

            $player = $transaction->getSource();
            $success = false;

            foreach ($book->getEnchantments() as $enchant) {
                $is_customenchant = $enchant->getType() instanceof CustomEnchants;
                if (!$is_customenchant || $this->plugin->canBeEnchanted($equipment, $enchant, $enchant->getLevel()) === true) {//TODO: Check XP
                    $success = true;
                    if ($is_customenchant) {
                        $equipment = $this->plugin->addEnchantment($equipment, $enchant->getId(), $enchant->getLevel());
                    } else {
                        $equipment->addEnchantment($enchant);
                    }
                } else {
                    $player->sendTip(TextFormat::RED . "The item is not compatible with this enchant.");
                }
            }

            if ($success) {
                $event->setCancelled();
                $book->pop();
                $equipment_action->getInventory()->setItem($equipment_action->getSlot(), $equipment);
                $enchantedbook_action->getInventory()->setItem($enchantedbook_action->getSlot(), $book);
                $player->sendTip(TextFormat::GREEN . "Enchanting succeeded.");
            }
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
        if (isset($this->plugin->flying[$player->getLowerCaseName()]) || $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::SPIDER) !== null) {
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
            if (isset($this->plugin->flying[$player->getLowerCaseName()]) || $player->getArmorInventory()->getChestplate()->getEnchantment(CustomEnchantsIds::SPIDER) !== null) {
                $event->setCancelled();
            }
        }
    }

    /**
     * @param PlayerMoveEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        if (isset($this->plugin->nofall[$player->getLowerCaseName()])) {
            if ($this->plugin->checkBlocks($player, 0, 1) !== true && $this->plugin->nofall[$player->getLowerCaseName()] < time()) {
                unset($this->plugin->nofall[$player->getLowerCaseName()]);
            } else {
                $this->plugin->nofall[$player->getLowerCaseName()]++;
            }
        }
        if ($from->getFloorX() == $player->getFloorX() && $from->getFloorY() == $player->getFloorY() && $from->getFloorZ() == $player->getFloorZ()) {
            $this->plugin->moved[$player->getLowerCaseName()] = 10;
            return;
        }
        $this->plugin->moved[$player->getLowerCaseName()] = 0;
        $this->checkGlobalEnchants($player, null, $event);
        $this->checkArmorEnchants($player, $event);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getLowerCaseName();
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
     * @param ProjectileHitBlockEvent $event
     *
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onHit(ProjectileHitBlockEvent $event)
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
                    $this->plugin->blockface[$player->getLowerCaseName()] = $packet->face;
                    break;
            }
        }
    }
}
