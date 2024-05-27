# ScoreTag Plugins

[![LICENSE](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![VERSION](https://img.shields.io/badge/version-2.0.0-green.svg)](https://semver.org)

This is a versatile plugin designed to display various player statistics and information on the scoreboard or HUD. It supports multiple economy systems and provides customizable tags for displaying player balances, online players, ping status, and more.

## Features
- Display player balances from multiple economy systems (EconomyAPI, BedrockEconomy, etc.).
- Show the number of online players and maximum players on the server.
- Visualize player ping status with customizable colors (red for high ping, yellow for moderate, green for low, and purple for very high).
- Support for external plugins such as RankSystem to display player ranks.

## Usage
1. Install the plugin in your PocketMine-MP server.
2. Customize the plugin settings and economy system in the `config.yml`.
3. Configure the ScoreHud plugin (if applicable) to display the provided tags.
4. Enjoy the updated scoreboard or HUD with player statistics!

## Tags List
You can use the following tags in your ScoreHud configuration:

- **{balances.player}**: Displays the player's balance from the configured economy system.
- **{coinapi.api}**: Displays the player's coin balance from the CoinAPI plugin.
- **{player.online}**: Shows the number of online players on the server.
- **{max.online}**: Shows the maximum number of players allowed on the server.
- **{ping.player}**: Displays the player's ping with customizable colors:
  - **Green**: Low ping
  - **Yellow**: Moderate ping
  - **Orange**: High ping
  - **Red**: Very high ping
- **{name.player}**: Displays the player's name.
- **{date.score}**: Shows the current date in `d-m-Y` format.
- **{rank.player}**: Displays the player's rank(s) if using the RankSystem plugin.

## Dependencies
- [libPiggyEconomy](https://github.com/DaPigGuy/libPiggyEconomy): A virion for easy support of multiple economy providers.
- BedrockEconomy or EconomyAPI
- RankSystem

## Support
For any issues or feature requests, please create an issue on the [ScoreTag Issues](https://github.com/ImmoDevs/ScoreTag/issues).

## Contribution
Contributions are welcome! If you have any improvements or new features to suggest, feel free to open a pull request on the [ScoreTag Pulls](https://github.com/ImmoDevs/ScoreTag/pulls).

## License
This plugin is licensed under the MIT License. See the [LICENSE](https://github.com/ImmoDevs/Score/blob/main/LICENSE) file for details.
