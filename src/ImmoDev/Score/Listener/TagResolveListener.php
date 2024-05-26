<?php

namespace ImmoDev\Score\Listener;

use Ifera\ScoreHud\event\TagsResolveEvent;
use ImmoDev\Score\Main;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use function count;
use function explode;
use function strval;

class TagResolveListener implements Listener {

    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onTagResolve(TagsResolveEvent $event) {
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
                $value = (string) $this->plugin->converter($this->plugin->money->myMoney($player));
                break;
            
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
                $value = (string) $player->getNetworkSession()->getPing();
                break;
            
            case "date":
                $date = date("d-m-Y");
                $value = (string) $date;
                break;
                
            case "rank":
                $rank = $player->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player)->getName();
                $value = (string) $rank;
                break;
        }

        $tag->setValue(strval($value));
    }
}