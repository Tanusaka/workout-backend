<?php
/**
 *
 * @author Samu
 */
namespace App\Libraries;

use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\RequestInterface;

use App\Models\Core\AuthModel;

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {

    private const HTTP_401 = 401;
    private const HTTP_403 = 403;
    private const HTTP_404 = 404;
    private const HTTP_500 = 500;

    private static $token = null;

    public static $authmodel;


    public static function initAuth() {
        static::$authmodel = new AuthModel();
    }

    public static function attempt($credentials=[])
    {
        $auth = static::$authmodel->getAuth($credentials['email']);

        if( !isset($auth) ) {
            return false;
        }

        #check is user exist
        if( is_null($auth) ) {
        	return false;
        }

        #check user is active
		if( $auth['status'] !== 'A' ) {
			return false;
		}

        #check password verification
		if( !password_verify($credentials['password'], $auth['password']) ) {
			return false;
		}

        return static::$authmodel->updateLogin($credentials['email']);
    }

    public static function register($data=[])
    {       
        return is_null($data) ? false : ( static::$authmodel->insert($data) ? true : false );
    }

    public static function logout()
    {
        try {
            self::validateToken();
            return static::$authmodel->updateLogout(self::email());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getPermissions($guard=null)
    {   
        try {
            self::validateToken();
            if ($guard == null) {
                return static::$authmodel->getAllPermissions(self::email());
            } else {
                return static::$authmodel->getGuardPermissions(self::email(), $guard);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getRefreshTokens()
    {   
        try {
            self::validateToken('ref');
            return self::getTokens(self::email());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getTokens($email=null)
    {
        try {
            $tokens = [
                'accessToken' => self::getJWT($email),
                'refreshToken'=> self::getRefreshJWT($email)
            ];
            static::$authmodel->updateTokens($tokens, $email);
            return $tokens;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getRefreshCookie($refreshToken=null)
    {
        return [
            'name'     => 'refjwt',
            'value'    => $refreshToken,
            'expire'   => '86500',
            'domain'   => '',
            'path'     => '/',
            'prefix'   => 'api_',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    public static function email()
    {
        try {
            return self::getPayLoad()->email;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function tenantid()
    {
        try {
            return self::getPayLoad()->tenantid;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function check()
    {
        return static::$authmodel->isLoggedin(self::email());
    }

    public static function expiration()
    {
        return self::getPayLoad()->exp;
    }

    public static function hasToken()
    {
        $token = self::getHeaderJWT();
  
        if(is_null($token) || empty($token)) {
            return false;
        }

        return true;
    }

    public static function isTokenExpired()
    {
        if ( (self::expiration() - time()) < 0 ) {
            return true;
        }

        return false;
    }

    public static function isTokenMatched($tokenType=null)
    {
        if ($tokenType=="ref") {
            $token = static::$authmodel->getRefreshToken(self::email());
        } else {
            $token = static::$authmodel->getAccessToken(self::email());
        }

        return is_null($token) ? false : ( self::getHeaderJWT() === $token ? true : false );
    }

    public static function allows($guard=null)
    {
        try {
            $guardTokens = explode('-', $guard);
            $permissions = self::getPermissions($guardTokens[0]);

            if ( !is_null($permissions) ) {
                if ($guardTokens[1] == 'r') {
                    return $permissions->read;
                } else if ($guardTokens[1] == 'w') {
                    return $permissions->write;
                } else if ($guardTokens[1] == 'd') {
                    return $permissions->delete;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getResponse($response_code=500)
    {
        $response = service('response');

        if ( $response_code==401 ) {
            $response->setStatusCode(401);
            $response->setJson(['status' => '401', 'messages' => ['error' => 'Token Invalid.']]);
        } else if ($response_code==403) {
            $response->setStatusCode(403);
            $response->setJson(['status' => '403', 'messages' => ['error' => 'Permission Denied.']]);
        } else if ($response_code==404) {
            $response->setStatusCode(404);
            $response->setJson(['status' => '404', 'messages' => ['error' => 'Token Not Found.']]);
        } else {
            $response->setStatusCode(500);
            $response->setJson(['status' => '500', 'messages' => ['error' => 'Internal Server Error.']]);
        }

        return $response;
    }

    private static function getHeaderJWT()
    {
        $header = service('request')->getHeader("Authorization");
        $token = null;
  
        if(!empty($header)) {
            #extract the token from the header
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }
  
        return $token;
    }

    private static function getPayLoad($token=null)
    {
        if( is_null(self::$token) ) { 
            $token = self::getHeaderJWT();
        } else {
            $token = self::$token;
        }

        if(is_null($token) || empty($token)) {
            throw new \Exception(self::HTTP_404);
        }

        $tokenparts = explode('.', $token);
        $payload = base64_decode($tokenparts[1]);

        return json_decode($payload);  
    }

    private static function getJWT($email=null)
	{
		#return jwt from here
		$iat = time(); 
		$exp = $iat + getenv('jwt.timeout');

		$payload = array(
			"iss" => getenv('jwt.iss'),
			"aud" => getenv('jwt.aud'),
			"sub" => getenv('jwt.sub'),
			"iat" => $iat, //Time the JWT issued at
			"exp" => $exp, // Expiration time of token
            "tenantid" => 1,
			"email" => $email,
		);
		
		return JWT::encode($payload, getenv('jwt.secret'), 'HS256');
	}

    private static function getRefreshJWT($email=null)
    {
        #return refresh jwt from here
		$iat = time(); 
		$exp = $iat + getenv('jwt.refresh_timeout');

        $payload = array(
			"iat" => $iat, //Time the JWT issued at
			"exp" => $exp, // Expiration time of token
			"tenantid" => 1,
            "email" => $email,
		);
		
		return JWT::encode($payload, getenv('jwt.refresh_secret'), 'HS256');
    }

    private static function validateToken($tokenType="acc")
    {
        if ( !self::hasToken() ) {
            throw new \Exception(self::HTTP_404);
        }

        if ( self::isTokenExpired() ) {
            throw new \Exception(self::HTTP_401);
        }

        if ( !self::check() ) {
            throw new \Exception(self::HTTP_403);
        }

        if ( !self::isTokenMatched($tokenType) ) {
            throw new \Exception(self::HTTP_401);
        }
    }

}