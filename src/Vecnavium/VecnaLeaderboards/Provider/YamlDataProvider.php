<?php

namespace Vecnavium\VecnaLeaderboards\Provider;

use LogLevel;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use Vecnavium\VecnaLeaderboards\Main;

class YamlDataProvider
{
	/** @var Main */
	private $plugin;
	/** @var int */
	private $leaderboardRange;
	/** @var array */
	private $levels;

	/**
	 * YamlDataProvider constructor.
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
		$this->init();
	}

	private function init(){
		@mkdir($this->plugin->getDataFolder() . "data/");
		$config = $this->plugin->getConfig();
		if (!$config->exists("CONFIG_VERSION") || $config->get("CONFIG_VERSION") != 2){
			$this->plugin->getLogger()->log(LogLevel::WARNING,"Your config version is outdated. Please delete the config.yml file and restart the server.");
		}
		$this->plugin->saveDefaultConfig();
		$this->leaderboardRange = $this->plugin->getConfig()->get("leaderboard-top-length", 10);
		$this->levels = $this->plugin->getConfig()->get('levels', []);
	}

	/**
	 * @return int
	 */
	public function getUpdateInterval(): int {
		return (int)$this->plugin->getConfig()->get('leaderboard-timer', 60);
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public function getRankings(string $type): string
	{
		$stats = [];

		switch ($type) {
			case Main::LEADERBOARD_TYPE_STREAKS:
				$string = "streaks";
				break;
            case Main::LEADERBOARD_TYPE_DEATHS:
                $string = "deaths";
                break;
			case Main::LEADERBOARD_TYPE_KILLS:
			default:
				$string = "kills";
				break;
		}
		foreach (glob($this->plugin->getDataFolder() . "data" . DIRECTORY_SEPARATOR . "*.yml") as $playerFile) {
			$config = new Config($playerFile, Config::YAML);
			$stats[basename($playerFile, ".yml")] = $config->get($string, 0);
		}
		arsort($stats, SORT_NUMERIC);
		$finalRankings = "";
		$i = 1;
		foreach ($stats as $name => $number) {
			$finalRankings .= C::RED . $i . ") " . $name . ": " . $number . "\n";
			if ($i > $this->leaderboardRange) {
				return $finalRankings;
			}
			if (count($stats) <= $i) {
				return $finalRankings;
			}
			$i++;
		}
		return "";
	}

	/**
	 * @return array
	 */
	public function getLevels(): array
	{
		return $this->levels;
	}
}
