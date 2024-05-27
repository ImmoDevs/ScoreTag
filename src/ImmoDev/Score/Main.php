<?php

namespace ImmoDev\Score;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\ClosureTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

use ImmoDev\Score\Listener\TagResolveListener;
use Ifera\Scorehud\Scorehud;
use Ifera\Scorehud\event\PlayerTagsUpdateEvent;
use Ifera\Scorehud\scoreboard\ScoreTag;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;

class Main extends PluginBase implements Listener {

    public $economyProvider;
    public $coin;
    private $customPing = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        libPiggyEconomy::init();

        $economyType = $this->getConfig()->get("economy");
        $this->economyProvider = libPiggyEconomy::getProvider($economyType);

        if (!class_exists(libPiggyEconomy::class)) {
            $this->getLogger()->error("libPiggyEconomy Virions not found!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        if ($this->economyProvider === null) {
            $this->getLogger()->error("Economy Provider Type '$economyType' not found");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        
        $this->coin = $this->getServer()->getPluginManager()->getPlugin("CoinAPI");

        if ($this->coin === null) {
            $this->getLogger()->alert("CoinAPI not found!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        if (class_exists(ScoreHud::class)) {
            $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
                function(): void {
                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                        $this->economyProvider->getMoney($player, function ($balances) use ($player) {
                            if ($balances === null) {
                                $balances = 0.0;
                            }

                            $coinValue = $this->converter($this->coin->myCoin($player));
                            $onlinePlayers = count($player->getServer()->getOnlinePlayers());
                            $maxPlayers = $player->getServer()->getMaxPlayers();
                            $ping = $this->getCustomPing($player);
                            $name = $player->getName();
                            $date = date("d-m-Y");

                            // Rank System Integration
                            $rankSystem = $this->getServer()->getPluginManager()->getPlugin("RankSystem");
                            if ($rankSystem !== null) {
                                $sessionManager = $rankSystem->getSessionManager();
                                $rankObjects = $sessionManager->get($name)->getRanks();
                                $ranks = [];
                                foreach ($rankObjects as $rankObject) {
                                    $ranks[] = $rankObject->getName();
                                }
                                $rank = count($ranks) > 0 ? implode(", ", $ranks) : "No Rank";
                            } else {
                                $rank = "N/A";
                            }

                            // Ping color coding
                            $pingColor = "§f"; // Default white
                            if ($ping < 100) {
                                $pingColor = "§a"; // Green
                            } elseif ($ping < 200) {
                                $pingColor = "§e"; // Yellow
                            } elseif ($ping < 300) {
                                $pingColor = "§6"; // Orange
                            } elseif ($ping >= 300) {
                                $pingColor = "§c"; // Red
                            }

                            $tags = [
                                new ScoreTag("balances.player", (string) $balances),
                                new ScoreTag("coinapi.api", (string) $coinValue),
                                new ScoreTag("player.online", (string) $onlinePlayers),
                                new ScoreTag("max.online", (string) $maxPlayers),
                                new ScoreTag("ping.player", $pingColor . $ping),
                                new ScoreTag("name.player", (string) $name),
                                new ScoreTag("date.score", (string) $date),
                                new ScoreTag("rank.player", (string) $rank)
                            ];

                            (new PlayerTagsUpdateEvent($player, $tags))->call();
                        });
                    }
                }
            ), 20); // Updated to 20 ticks (1 second)
        }
    }
    
    public function converter($n, $precision = 1): string {
        $n_format = number_format($n, $precision, '.', ',');

        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }
        return $n_format;
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "testping") {
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
                return false;
            }

            if (count($args) !== 1) {
                $sender->sendMessage(TextFormat::RED . "Usage: /testping <ping value|default>");
                return false;
            }

            if ($args[0] === "default") {
                $this->resetCustomPing($sender);
                $sender->sendMessage(TextFormat::GREEN . "Your ping has been reset to default.");
                return true;
            }

            if (!is_numeric($args[0])) {
                $sender->sendMessage(TextFormat::RED . "Usage: /testping <ping value|default>");
                return false;
            }

            $pingValue = (int) $args[0];
            $this->setCustomPing($sender, $pingValue);
            $sender->sendMessage(TextFormat::GREEN . "Your ping has been set to " . $pingValue);
            return true;
        }

        return false;
    }
    
    public function setCustomPing(Player $player, int $ping): void {
        $this->customPing[$player->getName()] = $ping;
    }

    public function resetCustomPing(Player $player): void {
        unset($this->customPing[$player->getName()]);
    }

    public function getCustomPing(Player $player): string {
        return isset($this->customPing[$player->getName()]) ? (string) $this->customPing[$player->getName()] : (string) $player->getNetworkSession()->getPing();
    }

    public function getEconomyProvider() {
        return $this->economyProvider;
    }
}
