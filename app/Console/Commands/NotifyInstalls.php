<?php

namespace App\Console\Commands;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Cache\CacheManager;
use Symfony\Component\DomCrawler\Crawler;
use App\Notifications\InstallsCountReachedNotification;

class NotifyInstalls extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'package-installs:notify';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check count of installs and notify';

	/**
	 * @var array
	 */
	protected $packages = [];

	/**
	 * @var CacheManager
	 */
	protected $cache;

	/**
	 * @var array
	 */
	protected $notified = [
		'users'    => 0,
		'packages' => 0,
	];


	/**
	 * @throws \Exception
	 */
	public function handle(): void
	{
		$this->readConfig();

		foreach ($this->packages as $package) {
			$this->processPackage($package);
		}

		$this->output->newLine();
		$this->info("Notified {$this->notified['users']} users with {$this->notified['packages']} packages.");
	}


	/**
	 * @throws \Exception
	 */
	protected function readConfig(): void
	{
		$this->packages = config('packages-to-notify');
		$this->cache = cache();
	}


	/**
	 * @param $package
	 */
	protected function processPackage($package): void
	{
		$http = new Client;

		$html = $http->get($package['url'])->getBody()->getContents();
		$crawler = new Crawler($html);

		$installs = $crawler->filter('.package-aside')->filter('.facts')->filter('p')->first();

		$installsCount = (int)preg_replace('/[A-z\n\s:]/', '', $installs->text());

		if (in_array($installsCount, $package['notify_on'])) {
			if (! $package['notify_duplicates'] && $this->cache->has($this->cacheKey($package['url'], $installsCount))) {
				return;
			} else {
				foreach ($package['notification_emails'] as $email) {
					(new User(compact('email')))->notify(new InstallsCountReachedNotification($package['url'], $installsCount));

					$this->notified['users']++;
				}
				$this->notified['packages']++;
			}

			$this->cache->forever($this->cacheKey($package['url'], $installsCount), true);
		}
	}


	/**
	 * @param $url
	 * @param $installs
	 *
	 * @return string
	 */
	protected function cacheKey($url, $installs)
	{
		return md5($url) . ':' . $installs;
	}
}
