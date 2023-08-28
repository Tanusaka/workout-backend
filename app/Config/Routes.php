<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
// $routes->get('/', 'Home::index');

/*
 * --------------------------------------------------------------------
 * Auth Routing
 * --------------------------------------------------------------------*/
$routes->get('/', 'Core/AuthController::index');
$routes->post('/auth/login', 'Core/AuthController::login');
$routes->post('/auth/signup', 'Core/AuthController::signup');
$routes->post('/auth/logout', 'Core/AuthController::logout');
$routes->post('/auth/permissions', 'Core/AuthController::permissions');
$routes->post('/auth/refreshtokens', 'Core/AuthController::refreshtokens');

/*
 * --------------------------------------------------------------------
 * User Management Routing
 * --------------------------------------------------------------------*/
$routes->get('/users', 'Core/UserController::index', ['filter' => 'authguard:users-r']);
$routes->post('/users/get', 'Core/UserController::get', ['filter' => 'authguard:users-r']);
$routes->post('/users/save', 'Core/UserController::save', ['filter' => 'authguard:users-w']);
$routes->post('/users/update', 'Core/UserController::update', ['filter' => 'authguard:users-w']);
$routes->post('/users/update/password', 'Core/UserController::update_password', ['filter' => 'authguard:users-w']);
$routes->post('/users/update/role', 'Core/UserController::update_role', ['filter' => 'authguard:users-w']);

$routes->get('/roles', 'Core/RoleController::index');
$routes->post('/roles/get', 'Core/RoleController::get', ['filter' => 'authguard:roles-r']);
$routes->post('/roles/permissions/update', 'Core/RoleController::updatePermissions', ['filter' => 'authguard:roles-w']);

/*
 * --------------------------------------------------------------------
 * Linked Profile Routing
 * --------------------------------------------------------------------*/
$routes->post('/linkedprofiles/get', 'App/Linkedprofile/LinkedprofileController::get', ['filter' => 'authguard:users-r']);
$routes->post('/linkedprofiles/get/users', 'App/Linkedprofile/LinkedprofileController::getUsersForLink', ['filter' => 'authguard:users-r']);
$routes->post('/linkedprofiles/save', 'App/Linkedprofile/LinkedprofileController::save', ['filter' => 'authguard:users-w']);
$routes->post('/linkedprofiles/delete', 'App/Linkedprofile/LinkedprofileController::delete', ['filter' => 'authguard:users-w']);



/*
 * --------------------------------------------------------------------
 * Course Routing
 * --------------------------------------------------------------------*/
$routes->get('/courses', 'App/Courses/CourseController::index', ['filter' => 'authguard:courses-r']);

$routes->post('/courses/get', 'App/Courses/CourseController::get', ['filter' => 'authguard:courses-r']);
$routes->post('/courses/save', 'App/Courses/CourseController::save', ['filter' => 'authguard:courses-w']);
$routes->post('/courses/update', 'App/Courses/CourseController::update', ['filter' => 'authguard:courses-w']);

$routes->post('/courses/sections/get', 'App/Courses/SectionController::get', ['filter' => 'authguard:courses-r']);
$routes->post('/courses/sections/save', 'App/Courses/SectionController::save', ['filter' => 'authguard:courses-w']);
$routes->post('/courses/sections/update', 'App/Courses/SectionController::update', ['filter' => 'authguard:courses-w']);

$routes->post('/courses/sections/lessons/get', 'App/Courses/LessonController::get', ['filter' => 'authguard:courses-r']);
$routes->post('/courses/sections/lessons/save', 'App/Courses/LessonController::save', ['filter' => 'authguard:courses-w']);
$routes->post('/courses/sections/lessons/update', 'App/Courses/LessonController::update', ['filter' => 'authguard:courses-w']);


/*
 * --------------------------------------------------------------------
 * Chats Routing
 * --------------------------------------------------------------------*/
$routes->get('/chats', 'App/ChatController::index', ['filter' => 'authguard:courses-r']);

$routes->post('/chats/get', 'App/ChatController::retrieveChatThread', ['filter' => 'authguard:courses-r']);
$routes->post('/chats/save', 'App/ChatController::save', ['filter' => 'authguard:courses-r']);
// $routes->post('/apps/chats/create', 'App/ChatController::save');


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
