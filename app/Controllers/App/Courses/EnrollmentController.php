<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\App\Courses\CourseController;
use App\Models\App\Courses\EnrollmentModel;

class EnrollmentController extends CourseController
{
    protected $enrollmentmodel;

    public function __construct() {
        parent::__construct();
        $this->enrollmentmodel = new EnrollmentModel();
    }

    public function index()
    {
        $user_id = $this->getAuthID();
        return $this->respond($this->successResponse(200, "", $this->enrollmentmodel->getEnrollments()), 200);
    }

    public function getCourseEnrollments()
    {
        try {

            $course_id = $this->request->getVar('courseid');

            if ( !isset($course_id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $enrollments = $this->enrollmentmodel->getCourseEnrollments($course_id);

            return $this->respond($this->successResponse(200, "", $enrollments), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function resetCourseEnrollments()
    {
        try {

            $course_id = $this->request->getVar('courseid');

            if ( !isset($course_id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $enrollments = $this->enrollmentmodel->getCourseEnrollments($course_id, 10);

            return $this->respond($this->successResponse(200, "", $enrollments), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getUserEnrollments()
    {
        try {

            $user_id = $this->request->getVar('userid');

            if ( !isset($user_id) ) {
                $user_id = $this->getAuthID();
            }

            $enrollments = $this->enrollmentmodel->getUserEnrollments($user_id);

            return $this->respond($this->successResponse(200, "", $enrollments), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getUsersForEnroll()
    {
        try {

            $course_id = $this->request->getVar('courseid');

            if ( !isset($course_id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $usersForEnroll = $this->enrollmentmodel->getUsersForEnroll($course_id);

            return $this->respond($this->successResponse(200, "", $usersForEnroll), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$courseEnrollment = [
				'courseid'=> trim($this->request->getVar('courseid')),
                'userid'=> trim($this->request->getVar('userid'))
			];

			if ( !$this->enrollmentmodel->saveCourseEnrollment($courseEnrollment) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $usersForEnroll = $this->enrollmentmodel->getUsersForEnroll($courseEnrollment['courseid']);

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED, $usersForEnroll), 200);
            
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function delete()
    {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $enrollment = $this->enrollmentmodel->getEnrollment($id);

            if ( empty($enrollment) ) {
                return $this->respond($this->errorResponse(404,"Course enrollment cannot be found."), 404);
            }

            if ( !$this->enrollmentmodel->delete(['id'=>$enrollment['id']]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $enrollments = $this->enrollmentmodel->getCourseEnrollments($enrollment['courseid']);

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED_DELETED, $enrollments), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function acceptEnrollment()
    {
        $id = trim($this->request->getVar('id'));

        if ( !isset($id) ) {
            return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
        }
        
        $extenrollment = $this->enrollmentmodel->getEnrollment($id);

        if ( empty($extenrollment) ) {
            return $this->respond($this->errorResponse(404,"Enrollment cannot be found."), 404);
        }

        $paymentInfo = $this->getCoursePaymentInfo($extenrollment['courseid']);

        if(!$paymentInfo['paymentrequired']) {

            $enrollment = [
                'enrolleddate'=> $this->getCurrentDateTimeString(),
                'status'=> 'A'
            ];
    
            if ( !$this->enrollmentmodel->updateCourseEnrollment($enrollment, $id) ) {
                return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
            }
    
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED_UPDATED, 
            ['enrollment'=>$this->enrollmentmodel->getEnrollment($id)]), 200);

        } else {
            return $this->respond($this->errorResponse(402,"Payment Required.",
            ['paymentinfo'=>$paymentInfo]), 402);
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
                'userid' => [
                    'label'  => 'User Id',
                    'rules'  => 'required'
				],
            ]);
        } elseif ( $type == 'delete' ) {
                $this->validation->setRules([
                    'id' => [
                        'label'  => 'Enrollment ID',
                        'rules'  => 'required'
                    ]
                ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}