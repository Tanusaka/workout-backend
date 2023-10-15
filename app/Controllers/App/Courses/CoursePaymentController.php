<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\CourseModel;
use App\Models\App\Courses\CoursePaymentsModel;

class CoursePaymentController extends AuthController
{
    protected $coursepaymentmodel;

    public function __construct() {
        parent::__construct();
        $this->coursepaymentmodel = new CoursePaymentsModel();
    }

    public function index()
    {
        $user_id = $this->getAuthID();
        return $this->respond($this->successResponse(200, "", $this->coursepaymentmodel->getCoursesPaymentsByUser($user_id), 200));
    }

    public function get()
    {
        try {
            $user_id = $this->getAuthID();
            $course_id = $this->request->getVar('courseid');

            if ( !isset($course_id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $coursePayment = $this->coursepaymentmodel->getCoursePaymentsByUser($user_id, $course_id);

            if ( is_null($coursePayment) ) {
                return $this->respond($this->errorResponse(404,"Course Payment cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $coursePayment), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getLastCoursePaymentByUser()
    {
        try {
            $user_id = $this->getAuthID();
            $course_id = $this->request->getVar('courseid');

            if ( !isset($course_id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $coursePayment = $this->coursepaymentmodel->getLastCoursePaymentByUser($user_id, $course_id);

            if ( is_null($coursePayment) ) {
                return $this->respond($this->errorResponse(404,"Course Payment cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $coursePayment), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$coursePayment = [
                'UserID'=> $this->getAuthID(),
                'Amount'=> trim($this->request->getVar('amount')),
                'PaymentMethod'=> trim($this->request->getVar('paymentmethod')),
                'PaymentReference'=> trim($this->request->getVar('paymentreference')),
                'subscritionid'=> trim($this->request->getVar('subscritionid')),
                'CourseID'=> trim($this->request->getVar('courseid')),
			];

			if ( !$this->coursepaymentmodel->saveCoursePayment($coursePayment) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_PAID, ['id'=>$this->coursepaymentmodel->getInsertID()]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
    {
        $this->setValidationRules('update');

        if ( $this->isValid() ) {        
            
            $user_id = $this->getAuthID();
        
            $courseid = trim($this->request->getVar('courseid'));

            $status = trim($this->request->getVar('status'));
			
            $extCourseEnrollment = $this->courseenrollmentsmodel->getEnrolledCourse($user_id, $courseid);

            if ( empty($extCourseEnrollment) ) {
                return $this->respond($this->errorResponse(404,"Course Enrollment cannot be found."), 404);
            }

			if ( !$this->courseenrollmentsmodel->updateCourseEnrollment($extCourseEnrollment, $status) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLL_UPDATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course Id',
                    'rules'  => 'required'
				],
                
            ]);
        } elseif ( $type == 'update' ) {
                $this->validation->setRules([
                    'courseid' => [
                        'label'  => 'Course Id',
                        'rules'  => 'required'
                    ],
                    
                ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}