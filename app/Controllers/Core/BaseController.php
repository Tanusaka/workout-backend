<?php

namespace App\Controllers\Core;

use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers: class Home extends BaseController
 *     
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{

	/**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $errors  = [];
    protected $helpers = ["form", "text"];

    protected $appconfigs;
	protected $validation;

    use ResponseTrait;
    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {	
    	// Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
        $this->appconfigs = config('App');
        $this->validation = \Config\Services::validation();
    }

    protected function getSuccessResponse($message='')
    {
        return [
            'status' => '200', 
            'messages' => [
                'success' => $message
                ]
            ];
    }

    protected function getErrorResponse($message='')
    {
        return [
            'status' => '400', 
            'messages' => [
                'error' => $message
                ]
            ];
    }

    protected function isValid()
    {
        $this->validation->withRequest($this->request)->run();

        if (!empty($this->validation->getErrors())) {
            $this->errors = $this->validation->getErrors();
            return false;
        } else {
            return true;
        }
    }
}