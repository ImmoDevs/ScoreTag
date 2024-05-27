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

            case "balances":
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
                $ping = (int) $this->plugin->getCustomPing($player);
                // Mengatur warna berdasarkan ping
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
                $value = $pingColor . (string) $ping;
                break;

            case "date":
                $value = date("d-m-Y");
                break;

            case "rank":
                $rankSystem = $this->plugin->getServer()->getPluginManager()->getPlugin("RankSystem");
                if ($rankSystem !== null) {
                    $sessionManager = $rankSystem->getSessionManager();
                    $rankObjects = $sessionManager->get($player->getName())->getRanks();
                    $ranks = [];
                    foreach ($rankObjects as $rankObject) {
                        $ranks[] = $rankObject->getName();
                    }
                    $value = count($ranks) > 0 ? implode(", ", $ranks) : "No Rank";
                } else {
                    $value = "N/A";
                }
                break;
        }

        $tag->setValue(strval($value));
    }
}
