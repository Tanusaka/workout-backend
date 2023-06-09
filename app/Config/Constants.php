<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code






defined('ER_MSG_INVALID_EMAIL_PASSWORD')     || define('ER_MSG_INVALID_EMAIL_PASSWORD', 'Invalid email or password.');
defined('ER_MSG_USER_ACCOUNT_DISABLED')      || define('ER_MSG_USER_ACCOUNT_DISABLED', 'User account is disabled.');






defined('API_MSG_SUCCESS_BASE_REQUEST')      || define('API_MSG_SUCCESS_BASE_REQUEST', 'Welcome to the Base API...');
defined('API_MSG_SUCCESS_LOGOUT')            || define('API_MSG_SUCCESS_LOGOUT', 'You have been successfully logged out.');
defined('API_MSG_SUCCESS_LOGOUT_ALREADY')    || define('API_MSG_SUCCESS_LOGOUT_ALREADY', 'You have been logged out already.');


defined('API_MSG_SUCCESS_USER_CREATED')      || define('API_MSG_SUCCESS_USER_CREATED', 'User has been created successfully.');
defined('API_MSG_SUCCESS_USER_UPDATED')      || define('API_MSG_SUCCESS_USER_UPDATED', 'User has been updated successfully.');
defined('API_MSG_SUCCESS_USER_DELETED')      || define('API_MSG_SUCCESS_USER_DELETED', 'User has been deleted successfully.');


defined('API_MSG_SUCCESS_COURSE_CREATED')    || define('API_MSG_SUCCESS_COURSE_CREATED', 'Course has been created successfully.');
defined('API_MSG_SUCCESS_COURSE_UPDATED')    || define('API_MSG_SUCCESS_COURSE_UPDATED', 'Course has been updated successfully.');
defined('API_MSG_SUCCESS_COURSE_DELETED')    || define('API_MSG_SUCCESS_COURSE_DELETED', 'Course has been deleted successfully.');

defined('API_MSG_SUCCESS_SECTION_CREATED')   || define('API_MSG_SUCCESS_SECTION_CREATED', 'Section has been created successfully.');
defined('API_MSG_SUCCESS_SECTION_UPDATED')   || define('API_MSG_SUCCESS_SECTION_UPDATED', 'Section has been updated successfully.');
defined('API_MSG_SUCCESS_SECTION_DELETED')   || define('API_MSG_SUCCESS_SECTION_DELETED', 'Section has been deleted successfully.');

defined('API_MSG_SUCCESS_LESSON_CREATED')   || define('API_MSG_SUCCESS_LESSON_CREATED', 'Lesson has been created successfully.');
defined('API_MSG_SUCCESS_LESSON_UPDATED')   || define('API_MSG_SUCCESS_LESSON_UPDATED', 'Lesson has been updated successfully.');
defined('API_MSG_SUCCESS_LESSON_DELETED')   || define('API_MSG_SUCCESS_LESSON_DELETED', 'Lesson has been deleted successfully.');

defined('API_MSG_SUCCESS_CHAT_CREATED')      || define('API_MSG_SUCCESS_CHAT_CREATED', 'Chat has been created successfully.');


defined('HTTP_500')                          || define('HTTP_500', 'Internal Server Error.');




defined('ER_MSG_UNAUTHORIZED_ACCESS')        || define('ER_MSG_UNAUTHORIZED_ACCESS', 'Access is denied due to privilege issue.');
defined('ER_MSG_UNEXPECTED_ERROR')           || define('ER_MSG_UNEXPECTED_ERROR', 'Unexpected error has been occured.');


defined('ER_MSG_INVALID_TOKEN')              || define('ER_MSG_INVALID_TOKEN', 'Invalid Token.');
defined('ER_MSG_INVALID_REQUEST')            || define('ER_MSG_INVALID_REQUEST', 'Invalid Request.');




defined('SC_MSG_USR_LOGOUT')                 || define('SC_MSG_USR_LOGOUT', 'User has been logged out successfully.');
