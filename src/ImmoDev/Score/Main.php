<?php

namespace ImmoDev\Score;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\event\player\{
    PlayerJumpEvent,
    PlayerJoinEvent
};

use ImmoDev\Score\Listener\TagResolveListener;
use Ifera\Scorehud\Scorehud;
use Ifera\Scorehud\event\PlayerTagsUpdateEvent;
use Ifera\Scorehud\scoreboard\ScoreTag;
use pocketmine\scheduler\ClosureTask;

class Main extends PluginBase implements Listener {

    public $money;
    public $coin;

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->coin = $this->getServer()->getPluginManager()->getPlugin("CoinAPI");

        if ($this->money === null) {
            $this->getLogger()->alert("EconomyAPI not found!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        
        if ($this->coin === null) {
            $this->getLogger()->alert("CoinAPI not found!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        if (class_exists(ScoreHud::class)) {
            $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
                function(): void {
                    foreach ($this->getServer()->getOnlinePlayers() as $player) {
                        if (!$player->isOnline()) {
                            continue;
                        }

                        $moneyValue = $this->converter($this->money->myMoney($player));
                        $coinValue = $this->converter($this->coin->myCoin($player));
                        $onlinePlayers = count($player->getServer()->getOnlinePlayers());
                        $maxPlayers = $player->getServer()->getMaxPlayers();
                        $ping = $player->getNetworkSession()->getPing();
                        $name = $player->getName();
                        $date = date("d-m-Y");
                        $rank = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player)->getName();
						// $mine = 
						// $kill = 
						// $death = 
                        
                        // TAGS
                        $tags = [
                            new ScoreTag("ecoapi.api", (string) $moneyValue),
                            new ScoreTag("coinapi.api", (string) $coinValue),
                            new ScoreTag("player.online", (string) $onlinePlayers),
                            new ScoreTag("max.online", (string) $maxPlayers),
                            new ScoreTag("ping.player", (string) $ping),
                            new ScoreTag("name.player", (string) $name),
                            new ScoreTag("date.score", (string) $date),
                            new ScoreTag("rank.player", (string) $rank)
                        ];

                        (new PlayerTagsUpdateEvent($player, $tags))->call();
                    }
                }
            ), 1);

            $this->getServer()->getPluginManager()->registerEvents(new TagResolveListener($this), $this);
        }
    }

	// public function MineRecord() {
	// }

	// public function KillRecord() {
	// }

	// public function DeathRecord() {
	// }

    public function converter($n, $precision = 1) {
        $n_format = number_format($n, $precision, '.', ',');

        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }
        return $n_format;
    }
}