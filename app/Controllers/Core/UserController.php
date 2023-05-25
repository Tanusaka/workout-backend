<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\AuthController;
use App\Models\Core\UserModel;

class UserController extends AuthController
{
    protected $usermodel;

    public function __construct() {
        parent::__construct();
        $this->usermodel = new UserModel();
    }

    public function index()
    {
        return $this->respond($this->usermodel->getUsers(), 200);
    }
}