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
 * User Routing
 * --------------------------------------------------------------------*/
$routes->get('/users', 'Core/UserController::index', ['filter' => 'authguard:users-r']);


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
