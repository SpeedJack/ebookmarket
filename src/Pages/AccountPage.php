<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use EbookMarket\{
	Entities\Token,
	Entities\User,
	Visitor,
	Exceptions\InvalidValueException,
};

class AccountPage extends AbstractPage
{
	public function actionIndex(): void
	{
		if (!$this->visitor->isLoggedIn())
			$this->redirect('/login');
		Visitor::assertMethod(Visitor::METHOD_GET);
		$this->setTitle('EbookMarket - ' . $this->visitor->user()->username);
		$this->addCss('form');
		$this->addCss('passwordcheck');
		$this->addJs('vendor/zxcvbn');
		$this->addJs('passwordcheck');
		$this->addJs('validation');
		$sessions = $this->visitor->user()->sessioncount;
		if ($sessions > 0)
			$sessions = $sessions - 1;
		$this->show('account/profile', [
			'user' => $this->visitor->user(),
			'sessioncount' => $sessions,
		]);
	}

	public function actionSecure(): void
	{
		if (!$this->visitor->isLoggedIn())
			$this->redirect('/login');
		$this->visitor->assertAjax();
		$authtoken = $this->visitor->cookie('authtoken');
		if ($authtoken === null)
			throw new InvalidValueException(
				'Submitted an invalid authtoken.',
				$this->visitor->getRoute(),
				'Not authorized.');
		$token = Token::get($authtoken);
		if ($token === null)
			throw new InvalidValueException(
				'Submitted a non-existent authtoken.',
				$this->visitor->getRoute(),
				'Not authorized.');
		$user = $token->authenticate($authtoken);
		if ($user === null || $user->id !== $this->visitor->user()->id)
			throw new InvalidValueException(
				'Submitted an invalid authtoken.',
				$this->visitor->getRoute(),
				'Not authorized.');
		$token->deleteOthers();
		$this->redirect('account/');
	}

	public function actionLogin(): void
	{
		if ($this->visitor->isLoggedIn())
			$this->redirectHome();
		$this->setActiveMenu('Login');
		switch (Visitor::getMethod()) {
		case Visitor::METHOD_POST:
			$this->visitor->assertAjax();
			$username = $this->visitor->param('username', Visitor::METHOD_POST);
			$password = $this->visitor->param('password', Visitor::METHOD_POST);
			$rememberme = $this->visitor->param('rememberme', Visitor::METHOD_POST);
			if (empty($username) || empty($password))
				throw new InvalidValueException(
					'Submitted an invalid username or password.',
					$this->visitor->getRoute(),
					'Invalid username or password. Maybe your account has been locked, check your email!');

			$user = User::get('username', $username);
			if (!$user)
				throw new InvalidValueException(
					'Submitted a wrong username or password.',
					$this->visitor->getRoute(),
					'Invalid username or password. Maybe your account has been locked, check your email!');

			$failed = !$user->verifyPassword($password);
			$shouldmail = $failed && $user->remainingattempts > 0;
			if ($failed) {
				$user->failLogin();
				$user->save();
			}
			if ($user->isLocked()) {
				$failed = true;
				if ($shouldmail)
					$this->sendmail($user->email, $user->username,
						'accountlocked', [
							'unlocktime' => date('j M Y H:i:s', $user->lastattempt + $this->app->config('lockout_time')),
						]);
			}
			if ($failed)
				throw new InvalidValueException(
					'Submitted a wrong username or password.',
					$this->visitor->getRoute(),
					'Invalid username or password. Maybe your account has been locked, check your email!');
			$user->remainingattempts = $this->app->config('max_login_attempts');
			$user->save();
			if (!$user->valid)
				throw new InvalidValueException(
					'Unverified user tryied to login.',
					$this->visitor->getRoute(),
					'Account not verified. Check your email.');

			$this->visitor->login($user, $rememberme === 'yes');
			$this->redirectHome();
		case Visitor::METHOD_GET:
			$this->addCss('form');
			$this->addJs('validation');
			$this->setTitle('EbookMarket - Login');
			$this->show('account/login');
		}
	}

	public function actionRegister(): void
	{
		if ($this->visitor->isLoggedIn())
			$this->redirectHome();
		$this->setActiveMenu('Sign Up');
		switch(Visitor::getMethod()) {
		case Visitor::METHOD_POST:
			$this->visitor->assertAjax();

			$verify = $this->visitor->param('verify', Visitor::METHOD_POST);
			$username = $this->visitor->param('username', Visitor::METHOD_POST);
			$email = $this->visitor->param('email', Visitor::METHOD_POST);
			$password = $this->visitor->param('password', Visitor::METHOD_POST);

			if (!empty($verify)) {
				if (empty($email) || !User::validateEmail($email))
					$this->replyJson([
						'invalid' => true,
						'inuse' => false,
					]);
				$user = User::get('email', strtolower($email));
				$this->replyJson([
					'invalid' => false,
					'inuse' => isset($user),
				]);
			}

			$this->visitor->assertCaptcha();

			if (empty($username) || empty($password) || empty($email))
				throw new InvalidValueException(
					'Submitted an invalid form.',
					$this->visitor->getRoute(),
					'Please, fill out all the required fields.');
			$email = strtolower($email);
			if (!User::validateUsername($username))
				throw new InvalidValueException(
					'Submitted an invalid username.',
					$this->visitor->getRoute(),
					'Invalid username.');
			if (!User::validateEmail($email))
				throw new InvalidValueException(
					'Submitted an invalid email.',
					$this->visitor->getRoute(),
					'Invalid email.');
			if (!User::validatePassword($password))
				throw new InvalidValueException(
					'Submitted an invalid password.',
					$this->visitor->getRoute(),
					'Invalid password.');

			$alreadyIn = user::getOr([
				'username' => $username,
				'email' => $email,
			]);
			if ($alreadyIn !== null
				&& strcasecmp($alreadyIn->email, $email) === 0)
				throw new InvalidValueException(
					'User trying to register with an already registered email: ' . $email . '.',
					$this->visitor->getRoute(),
					'User with this email already registered.');

			if ($alreadyIn !== null
				&& strcasecmp($alreadyIn->username, $username) === 0) {
				$this->sendmail($email, $alreadyIn->username, 'usernametaken');
			} else {
				$user = new User();
				$user->username = $username;
				$user->email = $email;
				$user->password = $password;
				$user->save();

				$user = User::get('username', $user->username);
				$verifyToken = Token::createNew($user, Token::VERIFY);
				$verifyToken->save();
				$verifyLink = $this->app->buildAbsoluteLink('/verify', [
					'token' => $verifyToken->usertoken,
				]);

				$this->sendmail($user->email, $user->username, 'verify', [
					'verifylink' => $verifyLink,
				]);
			}

			$this->show('message', [
				'title' => 'Account created!',
				'message' => 'We have sent you an email containing the instructions to complete the registration.',
			]);
		case Visitor::METHOD_GET:
			$this->enableRecaptcha();
			$this->addCss('form');
			$this->addCss("passwordcheck");
			$this->addJs('validation');
			$this->addJs('vendor/zxcvbn');
			$this->addJs('passwordcheck');
			$this->setTitle('EbookMarket - Register');
			$this->show('account/register');
		}
	}

	public function actionLogout(): void
	{
		if (!$this->visitor->isLoggedIn())
			$this->redirect('/login');
		Visitor::assertMethod(Visitor::METHOD_GET);
		if (!$this->visitor->verifyCsrfToken(Visitor::METHOD_GET))
			throw new InvalidValueException(
				'Invalid CSRF token during logout.',
				$this->visitor->getRoute(),
				'Invalid CSRF token.');
		$this->visitor->logout();
		$this->redirectHome();
	}

	public function actionRecovery(): void
	{
		if ($this->visitor->isLoggedIn())
			$this->redirectHome();
		switch(Visitor::getMethod()) {
		case Visitor::METHOD_GET:
			$this->enableRecaptcha();
			$this->setTitle('EbookMarket - Password Recovery');
			$this->addCss('form');
			$this->addJs('recovery');
			$this->show('account/recovery');
		case Visitor::METHOD_POST:
			$this->visitor->assertAjax();
			$this->visitor->assertCaptcha();
			$email = $this->visitor->param('email', Visitor::METHOD_POST);
			if(empty($email))
				throw new InvalidValueException(
					'Submitted an empty email during password recovery.',
					$this->visitor->getRoute(),
					'Invalid email address.');
			$user = User::get('email', $email);
			if($user === null)
				throw new InvalidValueException(
					'Submitted a wrong email during password recovery.',
					$this->visitor->getRoute(),
					'No account registered with this email address.');
			$token = Token::createNew($user, Token::RECOVERY);
			$token->save();
			$recoverylink = $this->app->buildAbsoluteLink('/changepwd', [
				'token' => $token->usertoken,
			]);
			$this->sendmail($user->email, $user->username, 'recovery', [
				'recoverylink' => $recoverylink,
			]);
			$this->show('message', [
				'title' => 'Check your email!',
				'message' => 'We have sent you an email with the instructions to recover your account.',
			]);
		}
	}

	public function actionChangepwd(): void
	{
		switch(Visitor::getMethod()) {
		case Visitor::METHOD_GET:
			if ($this->visitor->isLoggedIn())
				$this->redirectHome();
			$usertoken = $this->visitor->param('token', Visitor::METHOD_GET);
			if (empty($usertoken))
				throw new InvalidValueException(
					'User visited the recovery page with an empty token.',
					$this->visitor->getRoute(),
					'Invalid token.');
			$token = Token::get($usertoken);
			if ($token === null)
				throw new InvalidValueException(
					'User visited the recovery page with a non-existent token.',
					$this->visitor->getRoute(),
					'Invalid token.');
			if($token->authenticate($usertoken, Token::RECOVERY) === null)
				throw new InvalidValueException(
					'User visited the recovery page with an invalid token.',
					$this->visitor->getRoute(),
					'Invalid token.');
			$this->setTitle('EbookMarket - Change Password');
			$this->addCss('passwordcheck');
			$this->addJs('vendor/zxcvbn');
			$this->addJs('passwordcheck');
			$this->addCss('form');
			$this->addJs('validation');
			$this->show('account/changepassword', [
				'token' => $usertoken, // TODO: token reuse is ok for security?
			]);
		case Visitor::METHOD_POST:
			$this->visitor->assertAjax();
			$this->setTitle('EbookMarket - Password Changed');
			$password = $this->visitor->param('password', Visitor::METHOD_POST);
			if (empty($password) || !User::validatePassword($password))
				throw new InvalidValueException(
					'Submitted an invalid password.',
					$this->visitor->getRoute(),
					'Invalid password.');
			if ($this->visitor->isLoggedIn()) {
				if ($this->visitor->hasParam('token'))
					throw new InvalidValueException(
						'Submitted a recovery token when user is logged in.',
						$this->visitor->getRoute(),
						'Invalid request.');
				$oldpassword = $this->visitor->param('oldpassword', Visitor::METHOD_POST);
				if (empty($oldpassword))
					throw new InvalidValueException(
						'Submitted an empty old password.',
						$this->visitor->getRoute(),
						'Invalid password.');
				$user = $this->visitor->user();
				if (!$user->verifyPassword($oldpassword))
					throw new InvalidValueException(
						'Submitted an invalid old password.',
						$this->visitor->getRoute(),
						'Invalid password.');
				$user->password = $password;
				$user->remainingattempts = $this->app->config('max_login_attempts');
				$user->save();
				$this->sendmail($user->email, $user->username, 'passwordchanged');
				$this->show('message', [
					'title' => 'Password changed!',
					'message' => 'Your password has been successfully changed!',
				]);
			}
			if ($this->visitor->hasParam('oldpassword'))
				throw new InvalidValueException(
					'Submitted an old password when user is not logged in.',
					$this->visitor->getRoute(),
					'Invalid request.');
			$usertoken = $this->visitor->param('token', Visitor::METHOD_POST);
			if (empty($usertoken))
				throw new InvalidValueException(
					'Submitted an empty token.',
					$this->visitor->getRoute(),
					'Invalid token.');
			$token = Token::get($usertoken);
			if ($token === null)
				throw new InvalidValueException(
					'Submitted a non-existent token.',
					$this->visitor->getRoute(),
					'Invalid token.');
			$user = $token->authenticate($usertoken, Token::RECOVERY);
			if ($user === null)
				throw new InvalidValueException(
					'Submitted an invalid token.',
					$this->visitor->getRoute(),
					'Invalid token.');
			$user->password = $password;
			$user->save();
			$token->delete();
			$this->sendmail($user->email, $user->username, 'passwordchanged');
			$this->show('message', [
				'title' => 'Password changed!',
				'message' => 'Your password has been successfully changed! Now, <a href="' . $this->app->buildLink('/login') . '">Login into your account!</a>.',
			]);
		}
	}

	public function actionVerify(): void
	{
		if ($this->visitor->isLoggedIn())
			$this->redirectHome();
		Visitor::assertMethod(Visitor::METHOD_GET);
		$this->setTitle('EbookMarket - Account Verified');
		$usertoken = $this->visitor->param('token', Visitor::METHOD_GET);
		if (empty($usertoken))
			throw new InvalidValueException(
				'Received a verification request with an empty token.',
				$this->visitor->getRoute(),
				'Invalid token.');
		$token = Token::get($usertoken);
		if ($token === null)
			throw new InvalidValueException(
				'Received a verification request with an invalid token.',
				$this->visitor->getRoute(),
				'Invalid token.');
		$user = $token->authenticate($usertoken, Token::VERIFY);
		if($user === null)
			throw new InvalidValueException(
				'Received a verification request with a token not associated with an user.',
				$this->visitor->getRoute(),
				'Invalid token.');
		$user->valid = true;
		$user->save();
		$token->delete();
		$this->show('message', [
			'title' => 'Registration completed!',
			'message' => 'You have successfully verified your email address! Now, <a href="' . $this->app->buildLink('/login') . '">Login into your account!</a>.',
		]);
	}

	protected function buildSidebar(): ?string
	{
		return null;
	}
}
