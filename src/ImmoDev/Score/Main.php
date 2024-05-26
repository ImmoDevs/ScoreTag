<?php

namespace ImmoDev\Score;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use ImmoDev\Score\Listener\TagResolveListener;
use Ifera\Scorehud\Scorehud;
use Ifera\Scorehud\event\PlayerTagsUpdateEvent;
use Ifera\Scorehud\scoreboard\ScoreTag;
use DaPigGuy\libPiggyEconomy\libPiggyEconomy;
use pocketmine\scheduler\ClosureTask;

class Main extends PluginBase implements Listener {

    private $economyProvider;
    private $coin;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        libPiggyEconomy::init();

        $economyType = $this->getConfig()->get("economy");
        $this->economyProvider = libPiggyEconomy::getProvider(["provider" => $economyType]);

        if (!class_exists(libPiggyEconomy::class)) {
            $this->getLogger()->error("libPiggyEconomy Virions not found!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
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
                function (): void {
                    $allBalances = [];
                    $callbackLeft = count($this->getServer()->getOnlinePlayers());

                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                        $this->economyProvider->getMoney($player, function ($balances) use ($player, &$allBalances, &$callbackLeft) {
                            if ($balances === null) {
                                $balances = 0.0;
                            }
                            if (!$player->isOnline()) {
                                $callbackLeft--;
                                return;
                            }

                            $allBalances[$player->getName()] = $balances;
                            $callbackLeft--;

                            if ($callbackLeft === 0) {
                                $coinValue = $this->converter($this->coin->myCoin($player));
                                $onlinePlayers = count($this->getServer()->getOnlinePlayers());
                                $maxPlayers = $this->getServer()->getMaxPlayers();
                                $ping = $player->getNetworkSession()->getPing();
                                $name = $player->getName();
                                $date = date("d-m-Y");
                                $rankSystem = $this->getServer()->getPluginManager()->getPlugin("RankSystem");
                                $rank = $rankSystem !== null ? $rankSystem->getRank($player)->getName() : "N/A";

                                $pingColor = TextFormat::GREEN;
                                if ($ping >= 200) {
                                    $pingColor = TextFormat::DARK_PURPLE;
                                } elseif ($ping >= 100) {
                                    $pingColor = TextFormat::RED;
                                } elseif ($ping >= 50) {
                                    $pingColor = TextFormat::YELLOW;
                                }

                                $pingText = $pingColor . $ping;

                                // TAGS
                                $tags = [
                                    new ScoreTag("balances.player", (string) $balances),
                                    new ScoreTag("coinapi.api", (string) $coinValue),
                                    new ScoreTag("player.online", (string) $onlinePlayers),
                                    new ScoreTag("max.online", (string) $maxPlayers),
                                    new ScoreTag("ping.player", (string) $pingText),
                                    new ScoreTag("name.player", (string) $name),
                                    new ScoreTag("date.score", (string) $date),
                                    new ScoreTag("rank.player", (string) $rank)
                                ];

                                (new PlayerTagsUpdateEvent($player, $tags))->call();
                            }
                        });
                    }
                }
            ), 20);

            $this->getServer()->getPluginManager()->registerEvents(new TagResolveListener($this), $this);
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
}
