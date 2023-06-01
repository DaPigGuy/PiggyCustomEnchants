# PiggyCustomEnchants [![Poggit-CI](https://poggit.pmmp.io/shield.dl/PiggyCustomEnchants)](https://poggit.pmmp.io/p/PiggyCustomEnchants) [![Discord](https://img.shields.io/discord/330850307607363585?logo=discord)](https://discord.gg/qmnDsSD)

PiggyCustomEnchants is an open-sourced custom enchants plugin for [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) featuring over 90 custom enchantments.

<!-- If one question constantly persists, add the Q/A in here. -->
## FAQ
**Q:** How do I create/use an enchanted book? </br>
**A:** If you want to create an enchanted book, use the /ce enchant command on a normal book. Afterwards, you place the item you want to enchant on top of the enchanted book. It will then enchant your item.

## Prerequisites
* Basic knowledge on how to install plugins from Poggit Releases and/or Poggit CI
* PMMP 4.21.0+

## Installation & Setup
1. Install the plugin from Poggit.
2. (Optional) Configure your PiggyCE
   * Enchantment rarities, max levels, names, descriptions, & chances can be configured in their respective JSON files.
        * `chances.json`: Allows custom chance multipliers for any reactive enchantments.
            * Reaction chances are calculated by `chance multiplier * enchantment level`.
            * The chance multiplier by default is 100 for most enchantments.
   * Extra enchantment settings can be found under `extra_data.json`
   * The `config.yml` has many options. Some that you should pay attention to are:
     * `disabled-enchants`: Allows certain enchants to be disabled
     
         ```yaml
            disabled-enchants:
              - Porkified
         ```
     * `per-world-disabled-enchants`: Allows certain enchants to be disabled in specific worlds
     
         ```yaml
            per-world-disabled-enchants:
              # Disables Porkified & Volley in PlotWorld
              PlotWorld:
                - Porkified
                - Volley
              # Disables Jetpack in SurvivalWorld
              SurvivalWorld:
                - Jetpack
         ```
     * `world-damage`: Toggles world damage for explosive enchantments
3. (Optional) Install additional plugins supporting PiggyCE:
   * [PiggyCrates](https://poggit.pmmp.io/p/PiggyCrates) (Official)
   * [PiggyCustomEnchantsShop](https://poggit.pmmp.io/p/PiggyCustomEnchantsShop) (Official)
   * [PCEAllyChecks](https://poggit.pmmp.io/p/PCEAllyChecks) (Official)
   * [PCEBookShop](https://poggit.pmmp.io/p/PCEBookShop) (Official)
   * Kits
      * [EasyKits](https://poggit.pmmp.io/p/EasyKits)
      * [AdvancedKits](https://poggit.pmmp.io/p/AdvancedKits) 
      * [KitUI](https://poggit.pmmp.io/p/KitUI)
      * [KitsPlus](https://poggit.pmmp.io/p/KitsPlus)
   * [EnchantUI](https://poggit.pmmp.io/p/EnchantUI)
5. You're done! Start your server.

## Commands
| Command                  | Description                                     | Permissions                              | Aliases       |
|--------------------------|-------------------------------------------------|------------------------------------------|---------------|
| `/customenchant`         | Main command for PiggyCustomEnchants            | `piggycustomenchants.command.ce`         | `/ce`         |
| `/customenchant about`   | Shows version and author information            | `piggycustomenchants.command.ce.about`   | `/ce about`   |
| `/customenchant enchant` | Command to enchant an item with custom enchants | `piggycustomenchants.command.ce.enchant` | `/ce enchant` |
| `/customenchant help`    | Lists all PiggyCustomEnchant commands           | `piggycustomenchants.command.ce.help`    | `/ce help`    |
| `/customenchant info`    | Gives information on a custom enchant           | `piggycustomenchants.command.ce.info`    | `/ce info`    |
| `/customenchant list`    | Lists all PiggyCustomEnchants enchants          | `piggycustomenchants.command.ce.list`    | `/ce list`    |
| `/customenchant nbt`     | Outputs the NBT of the held item                | `piggycustomenchants.command.ce.nbt`     | `/ce nbt`     |
| `/customenchant remove`  | Removes a custom enchant from the held item     | `piggycustomenchants.command.ce.remove`  | `/ce remove`  |

## Permissions
| Permissions                              | Description                                                         | Default |
|------------------------------------------|---------------------------------------------------------------------|---------|
| `piggycustomenchants`                    | Allows usage of all PiggyCustomEnchants features                    | `op`    |
| `piggycustomenchants.command`            | Allow usage of all PiggyCustomEnchants commands                     | `op`    |
| `piggycustomenchants.command.ce`         | Allow usage of the /customenchant command                           | `op`    |
| `piggycustomenchants.command.ce.about`   | Allow usage of the /customenchant about subcommand                  | `true`  |
| `piggycustomenchants.command.ce.enchant` | Allow usage of the /customenchant enchant subcommand                | `op`    |
| `piggycustomenchants.command.ce.help`    | Allow usage of the /customenchant help subcommand                   | `true`  |
| `piggycustomenchants.command.ce.info`    | Allow usage of the /customenchant info subcommand                   | `true`  |
| `piggycustomenchants.command.ce.list`    | Allow usage of the /customenchant list subcommand                   | `true`  |
| `piggycustomenchants.command.ce.nbt`     | Allow usage of the /customenchant nbt subcommand                    | `true`  |
| `piggycustomenchants.command.ce.remove`  | Allow usage of the /customenchant remove subcommand                 | `op`    |
| `piggycustomenchants.overridecheck`      | Allow overriding of custom enchant level limit and item restriction | `false` |

## Issue Reporting
* If you experience an unexpected non-crash behavior with PiggyCustomEnchants, click [here](https://github.com/DaPigGuy/PiggyCustomEnchants/issues/new?assignees=DaPigGuy&labels=bug&template=bug_report.md&title=).
* If you experience a crash in PiggyCustomEnchants, click [here](https://github.com/DaPigGuy/PiggyCustomEnchants/issues/new?assignees=DaPigGuy&labels=bug&template=crash.md&title=).
* If you would like to suggest a feature to be added to PiggyCustomEnchants, click [here](https://github.com/DaPigGuy/PiggyCustomEnchants/issues/new?assignees=DaPigGuy&labels=suggestion&template=suggestion.md&title=).
* If you require support, please join our discord server [here](https://discord.gg/qmnDsSD).
* Do not file any issues related to outdated API version; we will resolve such issues as soon as possible.
* We do not support any spoons of PocketMine-MP. Anything to do with spoons (Issues or PRs) will be ignored.
  * This includes plugins that modify PocketMine-MP's behavior directly, such as TeaSpoon.

## Additional Information
* Detailed Plugin Setup, Custom Enchantment List, & API Documentation available at [PiggyDocs](https://piggydocs.aericio.net/PiggyCustomEnchants.html).

## License
```
   Copyright 2017 DaPigGuy

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

```
