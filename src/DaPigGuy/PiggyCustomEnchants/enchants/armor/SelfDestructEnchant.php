<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\armor;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use DaPigGuy\PiggyCustomEnchants\entities\PiggyTNT;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;

class SelfDestructEnchant extends ReactiveEnchantment
{
    /** @var string */
    public $name = "Self Destruct";

    /** @var int */
    public $usageType = CustomEnchant::TYPE_ARMOR_INVENTORY;
    /** @var int */
    public $itemType = CustomEnchant::ITEM_TYPE_ARMOR;

    public function getReagent(): array
    {
        return [PlayerDeathEvent::class];
    }

    public function getDefaultExtraData(): array
    {
        return ["tntAmountMultiplier" => 1];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof PlayerDeathEvent) {
            for ($i = 0; $i < $level * $this->extraData["tntAmountMultiplier"]; $i++) {
                $random = new Random();
                $tnt = new PiggyTNT($player->getLocation(), null, $this->plugin->getConfig()->getNested("world-damage.missile", false));
                $tnt->setFuse(40);
                $tnt->setOwningEntity($player);
                $tnt->setMotion(new Vector3($random->nextFloat() * 1.5 - 1, $random->nextFloat() * 1.5, $random->nextFloat() * 1.5 - 1));
                $tnt->spawnToAll();
            }
        }
    }
}