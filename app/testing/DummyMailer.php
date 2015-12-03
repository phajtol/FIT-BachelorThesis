<?php
/**
 * Created by PhpStorm.
 * User: petrof
 * Date: 12.3.2015
 * Time: 16:08
 */

namespace App\Testing;


use Nette\Mail\Message;

class DummyMailer implements \Nette\Mail\IMailer {
	/**
	 * Sends email.
	 * @return void
	 */
	function send(Message $mail)
	{
		// do nothing

		return;
	}


}