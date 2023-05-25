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

    private const TNF = "TOKEN404";

    private static $token = null;

    public static $authmodel;


    public static function initAuth() {
        static::$authmodel = new AuthModel();
    }

    public static function attempt($credentials=[])
    {
        $auth = static::$authmodel->getAuth($credentials['username']);

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

        static::$authmodel->updateLogin($credentials['username']);
        return true;
    }

    public static function register($data=[])
    {       
        return is_null($data) ? false : ( static::$authmodel->insert($data) ? true : false );
    }

    public static function logout()
    {
        if ( self::isTokenExpired() ) {
            throw new \Exception(self::TNF);
        }

        try {
            if ( !static::$authmodel->isLoggedin(self::username()) ) { 
                return false;
            }
            return static::$authmodel->updateLogout(self::username());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    public static function getJWT($username=null)
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
			"username" => $username,
		);
		
		return JWT::encode($payload, getenv('jwt.secret'), 'HS256');
	}

    public static function getRefreshJWT($username=null)
    {
        #return refresh jwt from here
		$iat = time(); 
		$exp = $iat + getenv('jwt.refresh_timeout');

        $payload = array(
			"iat" => $iat, //Time the JWT issued at
			"exp" => $exp, // Expiration time of token
			"username" => $username,
		);
		
		return JWT::encode($payload, getenv('jwt.refresh_secret'), 'HS256');
    }

    public static function getHeaderJWT()
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

    public static function updateToken($token=null)
    {
        # update refresh tokens...
        self::$token = $token;
        $authid = self::authid();
        
        //print_r('<pre>');print_r($authid);print_r('</pre>');die;

    }

    public static function username()
    {
        try {
            return self::getPayLoad()->username;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function expiration()
    {
        return self::getPayLoad()->exp;
    }

    public static function isTokenExpired()
    {
        if ( (self::expiration() - time()) < 0 ) {
            return true;
        }

        return false;
    }

    public static function getPermissions($guard=null)
    {   
        if ( self::isTokenExpired() ) {
            throw new \Exception(self::TNF);
        }

        try {
            if ($guard == null) {
                return static::$authmodel->getAllPermissions(self::authid());
            } else {
                return static::$authmodel->getGuardPermissions(self::authid(), $guard);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
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

    private static function getPayLoad($token=null)
    {
        if( is_null(self::$token) ) { 
            $token = self::getHeaderJWT();
        } else {
            $token = self::$token;
        }

        if(is_null($token) || empty($token)) {
            throw new \Exception(self::TNF);
        }

        $tokenparts = explode('.', $token);
        $payload = base64_decode($tokenparts[1]);

        return json_decode($payload);  
    }

    private static function authid()
    {   
        try {
            return static::$authmodel->getAuthID(self::username()); 
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private static function rtid()
    {   
        try {
            return static::$authmodel->getRtID(self::username()); 
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}