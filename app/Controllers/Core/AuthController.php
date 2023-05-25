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
				'username' => trim($this->request->getVar('username')),
				'password' => trim($this->request->getVar('password'))
			];
	
			if ( Auth::attempt($credentials) ) {

				$token = Auth::getJWT($credentials['username']);
				$refreshtoken = Auth::getRefreshJWT($credentials['username']);

				//Auth::updateToken($refreshtoken);

				$cookie = [
					'name'     => 'refjwt',
					'value'    => $refreshtoken,
					'expire'   => '86500',
					'domain'   => '',
					'path'     => '/',
					'prefix'   => 'api_',
					'secure'   => false,
					'httponly' => true,
					'samesite' => 'Lax',
				];
				
				$this->response->setCookie($cookie);
				$this->response->setJSON( [ 'token' => $token ] );
				
				return $this->response;

			} else {
				return $this->failUnauthorized(ER_MSG_INVALID_USERNAME_PASSWORD);
			}
			
		} else {
			return $this->failValidationErrors($this->errors);
		}		
	}

	public function permissions()
	{
		try {

			return $this->respond(Auth::getPermissions(), 200);

		} catch (\Exception $e) {
			log_message('error', '[ERROR] {exception}', ['exception' => $e]);
			
			if ($e->getMessage() == "TOKEN404") {
				return $this->failNotFound(API_MSG_ERROR_TNF);
			} 

			return $this->failServerError(API_MSG_ERROR_ISE);
		}
	}

	public function signup()
	{
		$this->setValidationRules('signup');

        if ( $this->isValid() ) {           
        
			$user = [
				'username'=> trim($this->request->getVar('username')), 
				'password'=> trim($this->request->getVar('password'))
			];

			if ( !Auth::register($user) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_USER_CREATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
	}

	public function logout()
	{
		try {
			if ( Auth::logout() ) {
				return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_LOGOUT), 200);
			} else {
				return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_LOGOUT_ALREADY), 200);
			}	
		} catch (\Exception $e) {
			log_message('error', '[ERROR] {exception}', ['exception' => $e]);
			
			if ($e->getMessage() == "TOKEN404") {
				return $this->failNotFound(API_MSG_ERROR_TNF);
			} 

			return $this->failServerError(API_MSG_ERROR_ISE);
		}
	}

	private function setValidationRules($type='')
    {
        if ( $type == 'signup' ) {
            $this->validation->setRules([
                'username' => [
                  'label'  => 'Email',
                  'rules'  => 'required|valid_email|is_unique[_auths.username]'
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
				'username' => [
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