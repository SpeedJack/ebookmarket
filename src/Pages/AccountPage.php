<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\{
	Entities\Token,
	Entities\User,
	Visitor,
	AppException
};

class AccountPage extends AbstractPage
{
	public const LOGIN = 1;
	public const REGISTER = 2;
	public const RECOVERY = 3;
	public const VERIFY = 4;

	public function actionIndex(): void
	{
		if (!$this->visitor->isLoggedIn())
			$this->redirect('/login');
		echo "Logged In";
	}

	public function actionLogin(): void
	{
		if ($this->visitor->isLoggedIn())
			$this->redirectHome();

		switch (Visitor::getMethod()) {
		case Visitor::METHOD_POST:
			$username = $this->visitor->param('username', Visitor::METHOD_POST);
			$password = $this->visitor->param('password', Visitor::METHOD_POST);
			$rememberme = $this->visitor->param('rememberme', Visitor::METHOD_POST);
			if (empty($username) || empty($password))
				$this->error('Error.', 'Invalid username or password.');

			$user = User::get('username', $username);
			if (!$user || !$user->verifyPassword($password))
				$this->error('Error.', 'Invalid username or password.');

			$this->visitor->login($user, $rememberme === 'yes');
			$this->redirectHome();
		case Visitor::METHOD_GET:
			$this->setTitle('EbookMarket - Login');
			$this->show('account/login');
		}
	}

	public function actionRegister(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET :
			$this->setTitle("EbookMarket - Register");
			$this->show("account/register");
			break;
		case Visitor::METHOD_POST :
			$username = $this->visitor->param("username", Visitor::METHOD_POST);
			$email = $this->visitor->param("email", Visitor::METHOD_POST);
			$password = $this->visitor->param("password", Visitor::METHOD_POST);
			$passwordConfirm = $this->visitor->param("password_confirm", Visitor::METHOD_POST);
			$accept = $this->visitor->param("accept_terms", Visitor::METHOD_POST);
			$validation = [
				"username" => !empty($username),
				"email" => !empty($email),
				"password" => !empty($password),
				"password_confirm" => ($password == $passwordConfirm),
				"accept_terms" => $accept === "on"
			];

			if(in_array(false, $validation) ||
                User::getOr(["username" => $username, "email" => $email])
            ) {
				$this->setTitle("EbookMarket - Register");
				$this->show("account/register");
			} else {
				$user = new User();
				$user->username = $username;
				$user->email = $email;
				$user->password = $password;
				$user->valid = false;

				$user->save();
				$user = User::get("username", $user->username);
				//Create AuthToken for email verification

				$verifyToken = Token::createNew($user, Token::VERIFY);
				$verifyToken->save();

				echo $verifyToken->usertoken;

				/**Send verification email

				echo $user->email;
				echo "Welcome to EbookMarket! \n
				please navigate to the following link for verify your account: \n
                https://"
				. $this->app->config("server_name")
				.":"
				. $this->app->config("server_port") ?? "443"
				. "/account/verify?token="
				. url_encode($verifyToken->usertoken);
				;**/
                $verifylink = "https://"
                    . $this->app->config("server_name")
                    .":"
                    . $this->app->config("server_port") ?? "443"
                    . "/account/verify?token="
                    . url_encode($verifyToken->usertoken);

                $this->sendmail($user->email, "mail/verify",
                    [
                        "username" => $this->username,
                        "verifylink" => $verifylink,
                    ]);
			}
			break;
		}
	}

	public function actionLogout(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET:
			$this->setTitle("EbookMarket - Logout");
			$this->show("authentication/logout");
			break;
		case Visitor::METHOD_POST:
			break;
		}
	}

	public function actionRecovery(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET:
			$this->setTitle("EbookMarket - Password Recovery");
			$this->show("account/recovery");
			break;
		case Visitor::METHOD_POST:
			$email = $this->visitor->param("email", Visitor::METHOD_POST);
			//$captcha = $this->visitor->param("captcha", Visitor::METHOD_POST);
			//if(VerifyCaptcha($captcha));
			if(!empty($email)){
				$user = User::get("email", $email);
				if($user){
					$token = Token::createNew($user, Token::RECOVERY);
					$token->save();
                    $recoverylink = "https://"
                        . $this->app->config("server_name")
                        .":"
                        . $this->app->config("server_port") ?? "443"
                        . "/account/changepassword?token="
                        . url_encode($token->usertoken);

                    $this->sendmail($user->email, "mail/recovery",
                        [
                            "username" => $this->username,
                            "recoverylink" => $recoverylink,
                        ]);
				}
			}
			break;
		}
	}

	public function actionChangepassword(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET:
			$usertoken = $this->visitor->param("token", Visitor::METHOD_GET);
			$token = Token::get($usertoken);
			if($token->authenticate($usertoken, Token::RECOVERY)){
				$this->setTitle("EbookMarket - Change Password");
				//verifica token e se valido show template
				$this->show("account/change_password", ["usertoken" => $usertoken]);
			}
			break;
		case Visitor::METHOD_POST:
			//Verifica token e cambio password
			$this->setTitle("EbookMarket - Password Change Result");
			$password = $this->visitor->param("password", Visitor::METHOD_POST);
			$passwordConfirm = $this->visitor->param("password_confirm", Visitor::METHOD_POST);
			$usertoken = $this->visitor->param("usertoken", Visitor::METHOD_POST);
			$token = Token::get($usertoken);

			if(!empty($password)
				&& !(empty($passwordConfirm))
				&& ($password == $passwordConfirm)
				&& $token->authenticate($usertoken, Token::RECOVERY)
				){
				$user = $token->getUser();
				if($user){
					$user->password = $password;
					$user->save();
					$this->show("account/password_change_result", ["success" => true]);
				} else {
					$this->show("account/password_change_result", ["success" => false]);
				}
				$token->delete();
			}
			break;
		}
	}

	public function actionVerify(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET:
			$this->setTitle("EbookMarket - Account Verification");
			$usertoken = $this->visitor->param("token", Visitor::METHOD_GET);
			$token = Token::get($usertoken);
			$user = $token->authenticate($usertoken, Token::VERIFY);
			if($user){
				$user->valid = true;
				$user->save();
				$token->delete();
				$this->show("account/verify_result",  ["success" => true]);
			} else {
				$this->show("account/verify_result",  ["success" => false]);
			}
			break;
		}
	}
}
