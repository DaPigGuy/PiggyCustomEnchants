<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyCustomEnchants\enchants\weapons\bows;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\ReactiveEnchantment;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\Event;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ShuffleEnchant extends ReactiveEnchantment
{
    public string $name = "Shuffle";
    public int $maxLevel = 1;

    public int $itemType = CustomEnchant::ITEM_TYPE_BOW;

    public function getReagent(): array
    {
        return [EntityDamageByChildEntityEvent::class];
    }

    public function react(Player $player, Item $item, Inventory $inventory, int $slot, Event $event, int $level, int $stack): void
    {
        if ($event instanceof EntityDamageByChildEntityEvent) {
            $entity = $event->getEntity();
            if ($entity instanceof Living) {
                $playerPosition = clone $player->getPosition();
                $entityPosition = clone $entity->getPosition();
                $player->teleport($entityPosition);
                $entity->teleport($playerPosition);
                $name = $entity->getNameTag();
                if (empty($name)) $name = $entity->getName();
                if ($entity instanceof Player) {
                    $name = $entity->getDisplayName();
                    $entity->sendMessage(TextFormat::DARK_PURPLE . "You have switched positions with " . $player->getDisplayName());
                }
                $player->sendMessage(TextFormat::DARK_PURPLE . "You have switched positions with " . $name);
            }
        }
    }
}