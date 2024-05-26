<?php

namespace ImmoDev\Score\Listener;

use Ifera\Scorehud\event\TagsResolveEvent;
use ImmoDev\Score\Main;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use function explode;
use function strval;

class TagResolveListener implements Listener {

    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onTagResolve(TagsResolveEvent $event): void {
        $tag = $event->getTag();
        $player = $event->getPlayer();
        $tags = explode('.', $tag->getName(), 2);
        $value = "";

        if (count($tags) < 2) {
            return;
        }

        switch ($tags[0]) {
            case "name":
                $value = $player->getName();
                break;

            case "ecoapi":
                $this->plugin->getEconomyProvider()->getMoney($player, function ($balances) use ($tag) {
                    if ($balances === null) {
                        $balances = 0.0;
                    }
                    $tag->setValue(strval($balances));
                });
                return;

            case "coinapi":
                $value = (string) $this->plugin->converter($this->plugin->coin->myCoin($player));
                break;

            case "player":
                $value = (string) count($player->getServer()->getOnlinePlayers());
                break;

            case "max":
                $value = (string) $player->getServer()->getMaxPlayers();
                break;

            case "ping":
                $ping = $player->getNetworkSession()->getPing();
                $pingColor = $this->getPingColor($ping);
                $value = $pingColor . (string) $ping;
                break;

            case "date":
                $value = date("d-m-Y");
                break;

            case "rank":
                $rankSystem = $this->plugin->getServer()->getPluginManager()->getPlugin("RankSystem");
                if ($rankSystem !== null) {
                    $rank = $rankSystem->getRank($player)->getName();
                    $value = (string) $rank;
                } else {
                    $value = "N/A";
                }
                break;
        }

        $tag->setValue(strval($value));
    }

    private function getPingColor(int $ping): string {
        if ($ping >= 200) {
            return "§5";
        } elseif ($ping >= 100) {
            return "§c";
        } elseif ($ping >= 50) {
            return "§e";
        } else {
            return "§a";
        }
    }
}
