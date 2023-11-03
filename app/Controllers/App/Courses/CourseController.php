<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\Core\UserModel;
use App\Models\Core\RoleModel;
use App\Models\App\Courses\CourseModel;
use App\Models\App\Courses\SectionModel;
use App\Models\App\Courses\LessonModel;
use App\Models\App\Courses\ReviewModel;
use App\Models\App\Courses\EnrollmentModel;
use App\Models\App\Courses\CoursePaymentsModel;


class CourseController extends AuthController
{
    protected $coursemodel;
    protected $sectionmodel;
    protected $lessonmodel;
    protected $enrollmentmodel;
    protected $usermodel;
    protected $rolemodel;
    protected $coursepaymentsmodel;

    public function __construct() {
        parent::__construct();
        $this->coursemodel = new CourseModel();
        $this->sectionmodel = new SectionModel();
        $this->lessonmodel = new LessonModel();
        $this->enrollmentmodel = new EnrollmentModel();
        $this->usermodel = new UserModel();
        $this->rolemodel = new RoleModel();
        $this->coursepaymentsmodel = new CoursePaymentsModel();
        // $reviewmodel = new ReviewModel();
    }

    public function index()
    {
        $rolename = $this->rolemodel->getRoleName($this->getAuthRoleID());

        if ( $rolename  == 'Super Administrator' || $rolename == 'Administrator' ) {
            return $this->respond($this->successResponse(200, "", $this->coursemodel->getCourses('A')), 200);
        } elseif ( $rolename  == 'Trainer' ) {
            return $this->respond($this->successResponse(200, "", $this->coursemodel->getCoursesByTrainer($this->getAuthID(), 'A')), 200);
        } else {
            return $this->respond($this->successResponse(200, "", $this->coursemodel->getCoursesByEnrollment($this->getAuthID(), 'A')), 200);
        }
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $course = [];

            $rolename = $this->rolemodel->getRoleName($this->getAuthRoleID());

            if ( $rolename  == 'Super Administrator' || $rolename == 'Administrator' ) {
                $course = $this->coursemodel->getCourse($id);
            } elseif ( $rolename  == 'Trainer' ) {
                $course = $this->coursemodel->getCourseByTrainer($id, $this->getAuthID());
            } else {
                $course = $this->coursemodel->getCourseByEnrollment($id, $this->getAuthID());
            }

            if ( empty($course) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

            $courseSections = []; $lessonCount = 0;
            $sections = $this->sectionmodel->getSections($course['id']);


            foreach ($sections as $section) {
                $section['lessons'] = $this->lessonmodel->getLessons($section['id'], 'A');
                array_push($courseSections, $section);
                $lessonCount = $lessonCount+ count($section['lessons']);
            }

            $course['sections'] = $courseSections;
            $course['totalSections'] = count($courseSections);
            $course['totalLessons'] = $lessonCount;
            $course['enrollments'] = $this->enrollmentmodel->getCourseEnrollments($course['id'], 10);
            $course['instructor'] = $this->usermodel->getUserProfile($course['instructorprofile']);

            return $this->respond($this->successResponse(200, "", $course), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getInstructors()
    {
        try {
            $id = $this->request->getVar('id');

            $insturctor = [];

            if ( isset($id) ) {
                $course = $this->coursemodel->getCourse($id);

                if ( empty($course) ) {
                    return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
                }
                
                $insturctor['current_instructor'] = $this->usermodel->getUserProfile($course['instructorprofile']);
                $insturctor['all_instructors'] = $this->usermodel->getTrainers();
            } else {

                if ( $this->rolemodel->getRoleName($this->getAuthRoleID()) == 'Trainer' ) { 
                    $insturctor['current_instructor'] = $this->usermodel->getUserProfile($this->getAuthID());
                    $insturctor['all_instructors'] = $this->usermodel->getTrainers();
                } else {
                    $insturctor['current_instructor'] = NULL;
                    $insturctor['all_instructors'] = $this->usermodel->getTrainers();
                }
                
            }

            return $this->respond($this->successResponse(200, "", $insturctor), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$course = [
                'tenantid'=> 1, //get this from token email
				'coursename'=> trim($this->request->getVar('coursename')),
                'courseintro'=> trim($this->request->getVar('courseintro')),
                'coursedescription'=> trim($this->request->getVar('coursedescription')),
                'courselevel'=> trim($this->request->getVar('courselevel')), 
                'coursetype'=> trim($this->request->getVar('coursetype')), 
                'courseimageid'=> trim($this->request->getVar('courseimageid')),
                'instructorprofile'=> trim($this->request->getVar('instructorprofile')),
                'priceplan'=> '',
                'price'=> '',
                'currencycode'=> '',
			];

			if ( !$this->coursemodel->saveCourse($course) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}
            
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_CREATED, 
            ['course'=>$this->coursemodel->getCourse($this->coursemodel->getInsertID())]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
    {
        $this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $courseid = trim($this->request->getVar('courseid'));
			
            $extcourse = $this->coursemodel->getCourse($courseid);

            if ( empty($extcourse) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

            $course = [
                'coursename'=> trim($this->request->getVar('coursename')),
                'courseintro'=> trim($this->request->getVar('courseintro')),
                'courselevel'=> trim($this->request->getVar('courselevel')),
                'coursetype'=> trim($this->request->getVar('coursetype')),
			];

            $courseimageid = trim($this->request->getVar('courseimageid'));
            if ($courseimageid!="") {
                $course['courseimageid'] = $courseimageid;
            }

			if ( !$this->coursemodel->updateCourse($course, $courseid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_UPDATED, 
            ['course'=>$this->coursemodel->getCourse($courseid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function updateDescription()
    {
        $this->setValidationRules('update_description');

        if ( $this->isValid() ) {           
        
            $courseid = trim($this->request->getVar('courseid'));
			
            $extcourse = $this->coursemodel->getCourse($courseid);

            if ( empty($extcourse) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

            $course = [
                'coursedescription'=> trim($this->request->getVar('coursedescription')),
			];

			if ( !$this->coursemodel->updateCourse($course, $courseid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_UPDATED, 
            ['course'=>$this->coursemodel->getCourse($courseid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function updateInstructor()
    {
        $this->setValidationRules('update_instructor');

        if ( $this->isValid() ) {           
        
            $courseid = trim($this->request->getVar('courseid'));
			
            $extcourse = $this->coursemodel->getCourse($courseid);

            if ( empty($extcourse) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

            $course = [
                'instructorprofile'=> trim($this->request->getVar('instructorprofile')),
			];

			if ( !$this->coursemodel->updateCourse($course, $courseid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $course = $this->coursemodel->getCourse($courseid);
            $course['instructor'] = $this->usermodel->getUserProfile($course['instructorprofile']);

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_UPDATED, 
            ['course'=>$course]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $course = $this->coursemodel->getCourse($id);

            if ( empty($course) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

			if ( !$this->coursemodel->delete(['id'=>$course['id']]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            #delete all contents
            $sections = $this->sectionmodel->getSections($course['id']);

            foreach ($sections as $section) {
                if ( !$this->lessonmodel->deleteLessonsBySection($section['id']) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }

                if ( !$this->sectionmodel->delete(['id'=>$section['id']]) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }
            }

            #delete all reviews
            // $reviews = $reviewmodel->getReviews($course['id']);
            // foreach ($reviews as $review) {
            //     if ( !$reviewmodel->delete(['id'=>$review['id']]) ) {
            //         return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
            //     }
            // }

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_DELETED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'coursename' => [
                    'label'  => 'Course Name',
                    'rules'  => 'required|is_unique[courses.coursename]'
				],
                'courseintro' => [
                    'label'  => 'Course Intro',
                    'rules'  => 'required'
				],
                'coursedescription' => [
                    'label'  => 'Course Description',
                    'rules'  => 'required'
                ],
                'courselevel' => [
                    'label'  => 'Course Level',
                    'rules'  => 'required'
                ],
                'coursetype' => [
                    'label'  => 'Course Type',
                    'rules'  => 'required'
                ],
                'courseimageid' => [
                    'label'  => 'Course Image',
                    'rules'  => 'required'
                ],
                'instructorprofile' => [
                    'label'  => 'Instructor Profile',
                    'rules'  => 'required'
                ]
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'coursename' => [
                    'label'  => 'Course Name',
                    'rules'  => 'required|is_unique[courses.coursename,id,{courseid}]'
				],
                'courseintro' => [
                    'label'  => 'Course Intro',
                    'rules'  => 'required'
				],
                'courselevel' => [
                    'label'  => 'Course Level',
                    'rules'  => 'required'
                ],
                'coursetype' => [
                    'label'  => 'Course Type',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'update_description' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'coursedescription' => [
                    'label'  => 'Course Description',
                    'rules'  => 'required'
                ],
            ]);
        } 
        elseif ( $type == 'update_instructor' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'instructorprofile' => [
                    'label'  => 'Instructor Profile',
                    'rules'  => 'required'
                ]
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    

    protected function getCoursePaymentInfo($id=0) {
        $course  = $this->coursemodel->getCourse($id);
        
        $coursePaymentInfo = [
            'paymentrequired' => true,
            'courseid' => $course['id'],
            'coursename' => $course['coursename'],
            'coursepriceplan' => $course['priceplan'],
            'courseprice' => $course['price'],
            'coursecurrency' => $course['currencycode']
        ];

        if(!$this->isPaymentRequired($course)) {
            $coursePaymentInfo['paymentrequired'] = false;
        } 

        return $coursePaymentInfo;
    }

    protected function isPaymentRequired($course=[])
    {
        try {

            $rolemodel = new RoleModel();
            $rolename = $rolemodel->getRoleName($this->getAuthRoleID());

            if ($rolename == 'Super Administrator' || $rolename == 'Administrator' || $rolename == 'Trainer') {
                return false;
            } else {

                if ($course['coursetype'] == 'FC') {
                    # free course...
                    return false;
                } else {
                    # paid course...
                    return $this->isPaymentPending($course);
                }

            }
            
        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return false;
        }
    }

    protected function isPaymentPending($course=[]) {

        $user_id = $this->getAuthID();

        if ($course['priceplan']=="OneTime") {
            
            $coursePayment = $this->coursepaymentsmodel->getLastCoursePaymentByUser($user_id, $course['id']);
            
            if(!empty($coursePayment)) {
                return false;
            } else {
                return true;
            }

        } else if ($course['priceplan']=="Monthly") {
            
            $coursePayment = $this->coursepaymentsmodel->getLastCoursePaymentByUser($user_id, $course['id']);

            if(!empty($coursePayment)) {
                $currentDateTime = new DateTime();
                $lastPaymentDateTime = new DateTime($coursePayment['CreatedAt']);

                $interval = $currentDateTime->diff($lastPaymentDateTime);

                $days = $interval->d;

                if($days > 31){
                    return true;                    
                } else {
                    return false;
                }
            } else {
                return true;
            }
            
        } else if ($course['priceplan']=="Yearly") {
            
            $coursePayment = $this->coursepaymentsmodel->getLastCoursePaymentByUser($user_id, $course['id']);
            
            if(!empty($coursePayment)){
                $currentDateTime = new DateTime();
                $lastPaymentDateTime = new DateTime($coursePayment['CreatedAt']);

                $interval = $currentDateTime->diff($lastPaymentDateTime);

                $days = $interval->d;

                if($days > 365){
                    return true;                   
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}