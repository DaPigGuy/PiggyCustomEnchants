<?php


namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;


use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class NBTSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    protected $plugin;

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $sender->sendMessage($sender->getInventory()->getItemInHand()->getNamedTag()->toString());
            return;
        }
        $sender->sendMessage(TextFormat::RED . "Please use this in-game.");
    }

    public function prepare(): void
    {
        $this->setPermission("piggycustomenchants.command.ce.nbt");
    }
}