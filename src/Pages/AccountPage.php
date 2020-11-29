<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use \EbookMarket\Entity\User;
use \EbookMarket\Visitor;
use \EbookMarket\AppException;

class AccountPage extends AbstractPage
{
	public const LOGIN = 1;
	public const REGISTER = 2;
	public const RECOVERY = 3;
	public const VERIFY = 4;

	public function actionIndex(): void
	{
		// TODO: this should show the user profile instead
		switch ($this->visitor->getMethod()) {
		case Visitor::METHOD_POST:
			throw new \LogicException('TODO: account/index');
		case Visitor::METHOD_GET:
		case Visitor::METHOD_HEAD:
			$this->app->reroute('auth/login');
			break;
		default:
			throw new AppException('Method not allowed.', 405);
		}
	}

	public function actionLogin(): void
	{
		if ($this->visitor->isLoggedIn())
			$this->app->redirectHome();

		switch ($this->visitor->getMethod()) {
		case Visitor::METHOD_POST:
			$username = $this->visitor->param('username', 'POST');
			$password = $this->visitor->param('password', 'POST');
			if (empty($username) || empty($password)) {
				$this->error('Error.', 'Invalid username or password.');
				return;
			}

			$user = User::get('username', $username);
			if (!$user || !$user->verifyPassword($password)) {
				$this->error('Error.', 'Invalid username or password.');
				return;
			}

			$this->visitor->authenticate($user);
			$this->redirectHome();
			break;
		case Visitor::METHOD_GET:
		case Visitor::METHOD_HEAD:
			$this->setTitle('EbookMarket - Login');
			$this->show('account/login');
			break;
		default:
			throw new AppException('Method not allowed.', 405);
		}
	}

    public function actionRegister(){ //TODO
        $method = $this->visitor->getMethod();
        switch($method) {
            case Visitor::METHOD_GET :
                $this->setTitle(__("EbookMarket - Register"));
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

                if(in_array(false, $validation) || User::getOr(["username" => $username, "email" => $email]) ){
                    $this->setTitle(__("EbookMarket - Register"));
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
            default : throw new \Exception("method" . $method . "not allowed");
            
      }
    }

    public function actionLogout(){ //TODO
        $method = $this->visitor->getMethod();
        switch($method) {
            case Visitor::METHOD_GET :
                $this->setTitle(__("EbookMarket - Logout"));
                $this->show("authentication/logout");
            break;
            case Visitor::METHOD_POST : 
            break;
            default : throw new \Exception("method" . $method . "not allowed");
            
      }
    }

    public function actionRecovery(){ //TODO
        $method = $this->visitor->getMethod();
        switch($method) {
            case Visitor::METHOD_GET :
                $this->setTitle(__("EbookMarket - Password Recovery"));
                $this->show("authentication/account_recovery");
            break;
            case Visitor::METHOD_POST : 
            break;
                default : throw new \Exception("method" . $method . "not allowed");
            
      }
    }

    public function actionVerify(){ //TODO
        $method = $this->visitor->getMethod();
        switch($method) {
            case Visitor::METHOD_GET :
                $this->setTitle(__("EbookMarket - Account Verification"));
                $this->show("authentication/account_verify");
            break;
            case Visitor::METHOD_POST : 
            break;
            default : throw new \Exception("method" . $method . "not allowed");
            
      }
    }
}
