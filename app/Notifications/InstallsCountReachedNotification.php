<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InstallsCountReachedNotification extends Notification
{
	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var int
	 */
	public $installs;


	/**
	 * Create a new notification instance.
	 *
	 * @param string $url
	 * @param int $installs
	 */
	public function __construct(string $url, int $installs)
	{
		$this->url = $url;
		$this->installs = $installs;
	}


	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return array
	 */
	public function via($notifiable)
	{
		return ['mail'];
	}


	/**
	 * Get the mail representation of the notification.
	 *
	 * @param  mixed $notifiable
	 *
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail($notifiable)
	{
		return (new MailMessage)
			->line("The package {$this->url} reached {$this->installs} installs!")
			->action('View on Packagist', $this->url)
			->line('Thank you for using our application!');
	}
}
