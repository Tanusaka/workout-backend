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
$routes->get('/users', 'Core/UserController::index', ['filter' => 'authguard:user_management']);
$routes->post('/users/get', 'Core/UserController::get', ['filter' => 'authguard:user_view_profile']);
$routes->post('/users/get/myprofile', 'Core/UserController::getMyProfile', ['filter' => 'authguard:user_view_profile']);
$routes->post('/users/get/trainers', 'Core/UserController::getTrainers', ['filter' => 'authguard:user_view_trainer_profile']);
$routes->post('/users/save', 'Core/UserController::save', ['filter' => 'authguard:user_create_profile']);
$routes->post('/users/update', 'Core/UserController::update', ['filter' => 'authguard:user_update_profile']);
$routes->post('/users/update/role', 'Core/UserController::updateRole', ['filter' => 'authguard:user_update_role']);
$routes->post('/users/update/password', 'Core/UserController::updatePassword', ['filter' => 'authguard:user_update_password']);
$routes->post('/users/update/description', 'Core/UserController::updateDescription', ['filter' => 'authguard:user_update_profile']);

$routes->post('/users/get/connections', 'Core/ConnectionController::get', ['filter' => 'authguard:user_view_connections']);
$routes->post('/users/get/connections/trainer', 'Core/ConnectionController::getTrainerConnections', ['filter' => 'authguard:user_add_connections']);
$routes->post('/users/get/connections/student', 'Core/ConnectionController::getStudentConnections', ['filter' => 'authguard:user_add_connections']);
$routes->post('/users/get/connections/parent', 'Core/ConnectionController::getParentConnections', ['filter' => 'authguard:user_add_connections']);
$routes->post('/users/get/connections/allroles', 'Core/ConnectionController::getUserRoleConnections', ['filter' => 'authguard:user_add_connections']);
$routes->post('/users/add/connection', 'Core/ConnectionController::save', ['filter' => 'authguard:user_add_connections']);
$routes->post('/users/delete/connection', 'Core/ConnectionController::delete', ['filter' => 'authguard:user_delete_connections']);


/*
 * --------------------------------------------------------------------
 * Role Management Routing
 * --------------------------------------------------------------------*/
$routes->get('/roles', 'Core/RoleController::index', ['filter' => 'authguard:role_management']);
$routes->post('/roles/get', 'Core/RoleController::get', ['filter' => 'authguard:role_view_permissions']);
$routes->post('/roles/permissions/update', 'Core/RoleController::updatePermission', ['filter' => 'authguard:role_update_permissions']);


/*
 * --------------------------------------------------------------------
 * File Management Routing
 * --------------------------------------------------------------------*/
$routes->get('/files', 'Core/FileController::index', ['filter' => 'authguard:file_management']);
$routes->post('/files/get', 'Core/FileController::get', ['filter' => 'authguard:file_view']);
$routes->post('/files/save', 'Core/FileController::save', ['filter' => 'authguard:file_create']);
$routes->post('/files/delete', 'Core/FileController::delete', ['filter' => 'authguard:file_delete']);


/*
 * --------------------------------------------------------------------
 * Course Routing
 * --------------------------------------------------------------------*/
$routes->get('/courses', 'App/Courses/CourseController::index', ['filter' => 'authguard:course_management']);

$routes->post('/courses/get', 'App/Courses/CourseController::get', ['filter' => 'authguard:course_view']);
$routes->post('/courses/get/instructors', 'App/Courses/CourseController::getInstructors', ['filter' => 'authguard:course_update']);

$routes->post('/courses/save', 'App/Courses/CourseController::save', ['filter' => 'authguard:course_create']);
$routes->post('/courses/update', 'App/Courses/CourseController::update', ['filter' => 'authguard:course_update']);
$routes->post('/courses/update/description', 'App/Courses/CourseController::updateDescription', ['filter' => 'authguard:course_update']);
$routes->post('/courses/update/instructor', 'App/Courses/CourseController::updateInstructor', ['filter' => 'authguard:course_update']);
$routes->post('/courses/delete', 'App/Courses/CourseController::delete', ['filter' => 'authguard:course_delete']);

$routes->post('/courses/sections/get', 'App/Courses/SectionController::get', ['filter' => 'authguard:course_view']);
$routes->post('/courses/sections/save', 'App/Courses/SectionController::save', ['filter' => 'authguard:course_update']);
$routes->post('/courses/sections/update', 'App/Courses/SectionController::update', ['filter' => 'authguard:course_update']);
$routes->post('/courses/sections/delete', 'App/Courses/SectionController::delete', ['filter' => 'authguard:course_update']);

$routes->post('/courses/sections/lessons/get', 'App/Courses/LessonController::get', ['filter' => 'authguard:course_view']);
$routes->post('/courses/sections/lessons/get/next', 'App/Courses/LessonController::getNext', ['filter' => 'authguard:course_view']);
$routes->post('/courses/sections/lessons/get/previous', 'App/Courses/LessonController::getPrevious', ['filter' => 'authguard:course_view']);
$routes->post('/courses/sections/lessons/save', 'App/Courses/LessonController::save', ['filter' => 'authguard:course_update']);
$routes->post('/courses/sections/lessons/update', 'App/Courses/LessonController::update', ['filter' => 'authguard:course_update']);
$routes->post('/courses/sections/lessons/delete', 'App/Courses/LessonController::delete', ['filter' => 'authguard:course_update']);

// $routes->post('/courses/sections/lessonduration/get', 'App/Courses/LessonDurationController::get', ['filter' => 'authguard:courses-r']);
// $routes->post('/courses/sections/lessonduration/save', 'App/Courses/LessonDurationController::save', ['filter' => 'authguard:courses-r']);
// $routes->post('/courses/sections/lessonduration/update', 'App/Courses/LessonDurationController::update', ['filter' => 'authguard:courses-r']);

$routes->get('/courses/enrollments/', 'App/Courses/EnrollmentController::index', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/course', 'App/Courses/EnrollmentController::getCourseEnrollments', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/course/reset', 'App/Courses/EnrollmentController::resetCourseEnrollments', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/user', 'App/Courses/EnrollmentController::getUserEnrollments', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/get/users', 'App/Courses/EnrollmentController::getUsersForEnroll', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/save', 'App/Courses/EnrollmentController::save', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/delete', 'App/Courses/EnrollmentController::delete', ['filter' => 'authguard:course_enroll_users']);
$routes->post('/courses/enrollments/accept', 'App/Courses/EnrollmentController::acceptEnrollment', ['filter' => 'authguard:course_enroll']);

$routes->post('/courses/payment/create', 'App/Courses/PaymentController::save', ['filter' => 'authguard:course_enroll']);

// $routes->get('/courses/payments/', 'App/Courses/CoursePaymentController::index', ['filter' => 'authguard:courses-r']);
// $routes->post('/courses/payments/get', 'App/Courses/CoursePaymentController::get', ['filter' => 'authguard:courses-r']);
// $routes->post('/courses/payments/get/last', 'App/Courses/CoursePaymentController::getLastCoursePaymentByUser', ['filter' => 'authguard:courses-r']);
// $routes->post('/courses/payments/save', 'App/Courses/CoursePaymentController::save', ['filter' => 'authguard:courses-r']);

/*
 * --------------------------------------------------------------------
 * Chats Routing
 * --------------------------------------------------------------------*/
$routes->get('/chats', 'App/Chats/ChatController::index', ['filter' => 'authguard:chat_management']);
$routes->post('/chats/get', 'App/Chats/ChatController::get', ['filter' => 'authguard:chat_management']);


$routes->post('/chats/save/personal', 'App/Chats/ChatController::savePersonalChat', ['filter' => 'authguard:chat_create']);
$routes->post('/chats/delete/personal', 'App/Chats/ChatController::deletePersonalChat', ['filter' => 'authguard:chat_delete']);
$routes->post('/chats/save/personal/message', 'App/Chats/ChatController::savePersonalChatMessage', ['filter' => 'authguard:chat_create']);
$routes->post('/chats/get/personal/connections', 'App/Chats/ChatController::getPersonalChatConnections', ['filter' => 'authguard:chat_create']);






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
