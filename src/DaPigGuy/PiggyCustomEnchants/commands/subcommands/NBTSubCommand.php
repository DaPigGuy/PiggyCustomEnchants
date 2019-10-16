<?php


namespace DaPigGuy\PiggyCustomEnchants\commands\subcommands;


use CortexPE\Commando\BaseSubCommand;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class NBTSubCommand
 * @package DaPigGuy\PiggyCustomEnchants\commands\subcommands
 */
class NBTSubCommand extends BaseSubCommand
{
    /** @var PiggyCustomEnchants */
    private $plugin;

    /**
     * NBTSubCommand constructor.
     * @param PiggyCustomEnchants $plugin
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(PiggyCustomEnchants $plugin, string $name, string $description = "", array $aliases = [])
    {
        $this->plugin = $plugin;
        parent::__construct($name, $description, $aliases);
    }

    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
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