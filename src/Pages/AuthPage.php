<?php

declare(strict_types=1);

namespace EbookMarket\Pages;

use \EbookMarket\Entity\User;
use \EbookMarket\Visitor;

class AuthPage extends AbstractPage
{
    public const LOGIN = 1;
    public const REGISTER = 2;
    public const RECOVERY = 3;
    public const VERIFY = 4;

    public function actionIndex() : void {
        $method = $this->visitor->getMethod();
        if($method == Visitor::METHOD_GET) {
             $this->app->reroute("auth/login");
        }
        else 
            throw new \Exception("method" . $method . "not allowed");     
      }
    public function actionLogin(){
        $method = $this->visitor->getMethod();
        switch($method) {
            case Visitor::METHOD_GET :
                $this->setTitle(__("EbookMarket - Login"));
                $this->show("authentication/login");
                break;
            case Visitor::METHOD_POST :
                $email = $this->visitor->param("email", "POST");
                $password = $this->visitor->param("password", "POST");
                echo $email . " " . $password;
                $user = User::get("email", $email);
                if(!$user || !password_verify($password, $user->passwordhash))
                {
                    $this->setTitle(__("EbookMarket - Login"));
                    $this->show("authentication/login");
                } else 
                {
                    $authToken = AuthToken::get(["user" => $user->id, "type" => "AUTHENTICATION"]);
                    if($authToken && $authToken->isExpired()){
                        $authToken->delete();
                        $authToken = null;
                    }
                    
                    if(!$authToken)
                        $authToken = new AuthToken(["type" => "AUTHENTICATION", "user" => $user->id]);
                    
                    $authToken->save();
                    setcookie("authtoken", $authToken->id,
                        [
                            "expires" => $authToken->expire_time,
                            "domain" => $this->app->config["domain"],
                            "secure" => true,
                            "httponly" => true    
                        ]
                    );
                    
                    $app->reroute("/book");
                }

                break;
            default : throw new \Exception("method " . $method . " not allowed");
            
      }
    }

    public function actionRegister(){
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

    public function actionLogout(){
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

    public function actionRecovery(){
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

    public function actionVerify(){
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
