<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\{
	Entity\User,
	Visitor,
	AppException,
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
			$username = $this->visitor->param('username', 'POST');
			$password = $this->visitor->param('password', 'POST');
			if (empty($username) || empty($password))
				$this->error('Error.', 'Invalid username or password.');

			$user = User::get('username', $username);
			if (!$user || !$user->verifyPassword($password))
				$this->error('Error.', 'Invalid username or password.');

			$this->visitor->login($user);
			$this->redirectHome();
		case Visitor::METHOD_GET:
			$this->setTitle('EbookMarket - Login');
			$this->show('account/login');
		}
	}

	//TODO
	public function actionRegister(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET :
			$this->setTitle("EbookMarket - Register");
			$this->show("authentication/register");
			break;
		case Visitor::METHOD_POST :
			$username = $this->visitor->param("username", "POST");
			$email = $this->visitor->param("email", "POST");
			$password = $this->visitor->param("password", "POST");
			$passwordConfirm = $this->visitor->param("password_confirm", "POST");
			$accept = $this->visitor->param("accept_terms", "POST");
			$validation = [
				"username" => User::validateUsername($username),
				"email" => User::validateEmail($email),
				"password" => User::validatePassword($password),
				"password_confirm" => $accept,
				"accept_terms" => ($password == $passwordConfirm)
			];

			if(in_array(false, $validation) || User::getOr(["username" => $username, "email" => $email])) {
				$this->setTitle("EbookMarket - Register");
				$this->show("authentication/register");
			} else {
				$user = new User();
				$user->username = $username;
				$user->email = $email;
				$user->password = password_hash($password);
				$user->valid = false;

				$user->save();

				//Create AuthToken for email verification

				$verifyToken = new AuthToken();
				$verifyToken->type = "VERIFY_EMAIL";
				$verifyToken->user = $user->id;
				$authToken->save();

				/**Send verification email
				mail($user->email, __("Account verification"),
				__("Welcome to EbookMarket! \n
				please navigate to the following link for verify your account: \n
				https://ebookmarket.com/auth/verify?token=". $authToken->$id
				));
				**/
			}
			break;
		default:
			throw new \Exception("method" . $method . "not allowed");
		}
	}

	//TODO
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
		default:
			throw new \Exception("method" . $method . "not allowed");
		}
	}

	//TODO
	public function actionRecovery(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET:
			$this->setTitle("EbookMarket - Password Recovery");
			$this->show("authentication/account_recovery");
			break;
		case Visitor::METHOD_POST:
			break;
		default:
			throw new \Exception("method" . $method . "not allowed");
		}
	}

	//TODO
	public function actionVerify(): void
	{
		$method = $this->visitor->getMethod();
		switch($method) {
		case Visitor::METHOD_GET:
			$this->setTitle("EbookMarket - Account Verification");
			$this->show("authentication/account_verify");
			break;
		case Visitor::METHOD_POST:
			break;
		default:
			throw new \Exception("method" . $method . "not allowed");
		}
	}
}
