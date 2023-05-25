<?php
  
namespace App\Filters;
  
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use App\Libraries\Auth;
  
class AuthGuard implements FilterInterface
{

    public function __construct() {
        Auth::initAuth();
  	}

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = Auth::getHeaderJWT();
  
        // check if token is null or empty
        if(is_null($token) || empty($token)) {
            return self::getResponse(404);
        }
  
        try {
            #check is token expired
            if ( Auth::isTokenExpired() ) {
                return self::getResponse(404);
            }

            #check permissions granted
            if ( isset($arguments[0]) && !Auth::allows($arguments[0]) ) {
                return self::getResponse(403);
            }
        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return self::getResponse(500);
        }
    }
  
    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }

    private static function getResponse($response_code=500)
    {
        $response = service('response');

        if ( $response_code==401 ) {
            $response->setStatusCode(401);
            $response->setJson(['status' => '401', 'messages' => ['error' => 'Access Denied.']]);
        } else if ($response_code==403) {
            $response->setStatusCode(403);
            $response->setJson(['status' => '403', 'messages' => ['error' => 'Permission Denied.']]);
        } else if ($response_code==404) {
            $response->setStatusCode(404);
            $response->setJson(['status' => '404', 'messages' => ['error' => 'Token Expired or Not Found.']]);
        } else {
            $response->setStatusCode(500);
            $response->setJson(['status' => '500', 'messages' => ['error' => 'Internal Server Error.']]);
        }

        return $response;
    }
}