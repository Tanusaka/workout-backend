<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\BaseController;
use App\Libraries\Auth;


class AuthController extends BaseController
{
	public function __construct() {
		Auth::initAuth();
  	}

  	public function index()
	{
		return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_BASE_REQUEST), 200);
	}

	public function login()
	{
		$this->setValidationRules('login');

		if ( $this->isValid() ) {	
			
			$credentials = [
				'email' => trim($this->request->getVar('email')),
				'password' => trim($this->request->getVar('password'))
			];

			try {
				if ( Auth::attempt($credentials) ) {
					
					$tokens = Auth::getTokens($credentials['email']);
					
					// $this->response->setCookie( Auth::getRefreshCookie($tokens['refreshToken']) );
	
					$authuser = Auth::getAuthUser($credentials['email']);

					$authuser['token'] = $tokens['accessToken'];
					$authuser['rtoken'] = $tokens['refreshToken'];
	
					$this->response->setJSON($authuser);
					
					return $this->response;
				
				} else {
					return $this->failUnauthorized(ER_MSG_INVALID_EMAIL_PASSWORD);
				}
			} catch (\Exception $e) {
				log_message('error', '[ERROR] {exception}', ['exception' => $e]);
				return $this->failServerError(HTTP_500);
			}
			
		} else {
			return $this->failValidationErrors($this->errors);
		}		
	}

	public function signup()
	{
		$this->setValidationRules('signup');

        if ( $this->isValid() ) {           
        
			$user = [
				'email'=> trim($this->request->getVar('email')), 
				'password'=> trim($this->request->getVar('password'))
			];

			if ( !Auth::register($user) ) {
				return $this->failServerError(HTTP_500);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_USER_CREATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
	}

	public function logout()
	{
		try {
			
			if ( !Auth::logout() ) {
				return $this->failServerError(HTTP_500);
			} 

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_LOGOUT), 200);

		} catch (\Exception $e) {
			log_message('error', '[ERROR] {exception}', ['exception' => $e]);
			return Auth::getResponse($e->getMessage());
		}
	}

	public function permissions()
	{
		try {
			return $this->respond(Auth::getPermissions(), 200);
		} catch (\Exception $e) {
			log_message('error', '[ERROR] {exception}', ['exception' => $e]);
			return Auth::getResponse($e->getMessage());
		}
	}

	public function refreshtokens()
	{
		try {

			$tokens = Auth::getRefreshTokens();
					
			$this->response->setCookie( Auth::getRefreshCookie($tokens['refreshToken']) );
			$this->response->setJSON( [ 'token' => $tokens['accessToken'] ] );
			
			return $this->response;

		} catch (\Exception $e) {
			log_message('error', '[ERROR] {exception}', ['exception' => $e]);
			return Auth::getResponse($e->getMessage());
		}
	}

	public function getAuthID()
	{
		return Auth::getAuthUserID();
	}

	public function getAuthRoleID()
	{
		return Auth::getAuthRoleID();
	}

	public function getAuthRole()
	{
		return Auth::getAuthRole();
	}

	private function setValidationRules($type='')
    {
        if ( $type == 'signup' ) {
            $this->validation->setRules([
                'email' => [
                  'label'  => 'Email',
                  'rules'  => 'required|valid_email|is_unique[_auths.email]'
                ],
                'password' => [
                  'label'  => 'Password',
                  'rules'  => 'required'
				],
				'confirm_password' => [
					'label'  => 'Confirm Password',
					'rules'  => 'required|matches[password]'
				]
            ]);
        } else if ( $type == 'login' ) { 
            $this->validation->setRules([
				'email' => [
					'label'  => 'Email',
					'rules'  => 'required|valid_email'
				],
				'password' => [
					'label'  => 'Password',
					'rules'  => 'required'
				]
			]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}