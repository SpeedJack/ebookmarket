<?php

declare(strict_types=1);

namespace EbookMarket\Services;

use EbookMarket\{
	App,
	Exceptions\ServerException,
};
use PHPMailer\PHPMailer\{
	PHPMailer,
	Exception,
};

require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/SMTP.php';

class MailerService
{
	public static function sendmail(string $to, string $toname,
		string $subject, string $txtmsg, ?string $htmlmsg = null,
		?array $params = null): void
	{
		$mailcfg = App::getInstance()->config('mail');
		if ($mailcfg['enable'] !== true)
			return;
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->Host = $mailcfg['smtp_host'];
			$mail->SMTPAuth = true;
			$mail->Username = $mailcfg['smtp_username'];
			$mail->Password = $mailcfg['smtp_password'];
			$mail->SMTPSecure = $mailcfg['smtp_security'];
			$mail->Port = $mailcfg['smtp_port'];

			$mail->CharSet = PHPMailer::CHARSET_UTF8;
			$mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;
			$mail->WordWrap = PHPMailer::STD_LINE_LENGTH;
			$mail->setFrom($mailcfg['from_address'],
				$mailcfg['from_name']);
			$mail->addAddress($to, $toname);
			$mail->Subject = $subject;
			if (!empty($htmlmsg)) {
				$mail->isHTML();
				$mail->Body = $htmlmsg;
				$mail->AltBody = $txtmsg;
			} else {
				$mail->Body = $txtmsg;
			}

			$mail->send();
		} catch (Exception $ex) {
			throw new ServerException('PHPMailer error.', null,
				'Can not send mail.', 500, $ex);
		}
	}
}
