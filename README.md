# PiggyCustomEnchants [![Poggit-CI](https://poggit.pmmp.io/ci.badge/DaPigGuy/PiggyCustomEnchants/PiggyCustomEnchants/master)](https://poggit.pmmp.io/ci/DaPigGuy/PiggyCustomEnchants) 

PiggyCustomEnchants is an open-sourced custom enchants plugin for PMMP.

## v2.0.0 changelog
Version 2.0.0 is a complete rewrite of the plugin and introduces tons of bug fixes, optimizations, and quality of life changes.
All custom enchantments in v1.4.6 have also been ported to v2.0.0.
* Enabling/disabling enchantments in certain worlds/globally is now built into the plugin. ([#94](https://github.com/DaPigGuy/PiggyCustomEnchants/issues/94))
* Added a remote enchant disabler for emergency situations (disable affects all servers).
* Custom enchantments that are disabled will not have their tasks enabled.
* Execution chances of enchantments are now configurable.
* ... Go to [PiggyDocs](https://piggydocs.aericio.net/PiggyCustomEnchants.html) for the complete changelog on v2.0.0.

<!-- If one question constantly persists, add the Q/A in here. -->
## FAQ
**Q:** How do I create/use an enchanted book? </br>
**A:** If you want to create an enchanted book, use the /ce enchant command on a normal book. Afterwards, you place the item you want to enchant on top of the enchanted book. It will then enchant your item.

**Q:** `ErrorException: "Invalid argument supplied for foreach()" (EXCEPTION) in "plugins/PiggyCustomEnchants.phar/src/DaPigGuy/PiggyCustomEnchants/PiggyCustomEnchants" at line 54` </br>
**A:** This is due to an outdated configuration from Version 1.0.0. You should delete your old configuration to allow PiggyCE to re-generate a new configuration.

**Q:** `'CortexPE\Commando\BaseCommand' not found in ...\PiggyCustomEnchants-master\src\DaPigGuy\PiggyCustomEnchants\commands\CustomEnchants` </br>
**A:** You MUST use the pre-compiled phar from [Poggit-CI](https://poggit.pmmp.io/ci/DaPigGuy/PiggyCustomEnchants/~) instead of downloading directly from GitHub.

## Information
* We do not support any spoons. Anything to do with spoons (Issues or PRs) will be ignored.
* You can find a list of the current custom enchants at [wiki](https://piggydocs.aericio.net/PiggyCustomEnchants.html).
* We are using [libFormAPI](https://github.com/jojoe77777/FormAPI) and [Commando](https://github.com/CortexPE/Commando) virions.
    * **You MUST use the pre-compiled phar from [Poggit-CI](https://poggit.pmmp.io/ci/DaPigGuy/PiggyCustomEnchants/~) instead of GitHub.**
    * If you wish to run it via source, check out [DEVirion](https://github.com/poggit/devirion).
* Are you a developer? Check out our API Documentation at [PiggyDocs](https://piggydocs.aericio.net/PiggyCustomEnchants.html).
    * v2.0.0 list of custom enchants are also at PiggyDocs.
* Check out our [Discord Server](https://discord.gg/qmnDsSD) for additional plugin support.
