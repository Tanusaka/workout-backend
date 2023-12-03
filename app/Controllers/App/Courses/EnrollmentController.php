<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\App\Courses\CourseController;

class EnrollmentController extends CourseController
{
    public function __construct() {
        parent::__construct();
    }

    public function get()
    {
        try {

            $course_id = $this->request->getVar('courseid');

            if ( !isset($course_id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $enrollments['coupon'] = $this->enrollmentcouponmodel->getCouponByCourse($course_id);
            $enrollments['enrollments'] = $this->enrollmentmodel->getEnrollments($course_id);

            return $this->respond($this->successResponse(200, "", $enrollments), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$enrollment = [
				'courseid'=> trim($this->request->getVar('courseid')),
                'userid'=> trim($this->request->getVar('userid'))
			];

			if ( !$this->enrollmentmodel->saveEnrollment($enrollment) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $enrollments = $this->enrollmentmodel->getEnrollments($enrollment['courseid']);

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED, $enrollments), 200);
            
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
                return $this->respond($this->errorResponse(404,"Enrollment cannot be found."), 404);
            }

            if ( !$this->enrollmentmodel->delete(['id'=>$enrollment['id']]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $enrollments = $this->enrollmentmodel->getEnrollments($enrollment['courseid']);

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED_DELETED, $enrollments), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function accept()
    {

        $this->setValidationRules('accept_coupon');

        if ( $this->isValid() ) {   
            
            $courseid = trim($this->request->getVar('courseid'));

            if ( !isset($courseid) ) {
                return $this->respond($this->errorResponse(400,['couponcode'=>'Invalid Request.']), 400);
            }

            if ( empty($this->coursemodel->getCourse($courseid)) ) {
                return $this->respond($this->errorResponse(400,['couponcode'=>'Invalid Request.']), 400);
            }

            if ( $this->enrollmentmodel->isEnrolled($courseid, $this->getAuthID()) ) {
                return $this->respond($this->errorResponse(400,['couponcode'=>'Invalid Request.']), 400);
            }

            $couponcode = trim($this->request->getVar('couponcode'));

            if (!$this->enrollmentcouponmodel->validateCoupon($courseid, $couponcode)) {
                return $this->respond($this->errorResponse(400,['couponcode'=>'Invalid Coupon Code.']), 400);
            }

            $enrollment = [
				'courseid'=> $courseid,
                'userid'=> $this->getAuthID()
			];

			if ( !$this->enrollmentmodel->saveEnrollment($enrollment) ) {
                return $this->respond($this->errorResponse(400,['couponcode'=>'Internal Server Error.']), 400);
			}

            $paymentInfo = $this->getCoursePaymentInfo($courseid);

            if($paymentInfo['paymentrequired']) {
                return $this->respond($this->errorResponse(402,"Payment Required.", ['paymentinfo'=>$paymentInfo]), 402);
            }

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED_UPDATED), 200);

        } else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
        
    }

    public function updateCoupon()
	{
		$this->setValidationRules('update_coupon');

        if ( $this->isValid() ) {   

            $courseid = trim($this->request->getVar('courseid'));

            if ( empty($this->coursemodel->getCourse($courseid)) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }
            
            $extcoupon = $this->enrollmentcouponmodel->getCouponByCourse($courseid);
        
            if (empty($extcoupon)) {
                $newcoupon = [
                    'courseid'=> $courseid,
                    'couponcode'=> trim($this->request->getVar('couponcode')),
                    'maxattempts'=> trim($this->request->getVar('maxattempts')),
                    'status'=> trim($this->request->getVar('couponstatus')),
                ];

                $coupon = $this->enrollmentcouponmodel->saveCoupon($newcoupon);
            } else {
                $extcoupon['couponcode'] = trim($this->request->getVar('couponcode'));
                $extcoupon['maxattempts'] = trim($this->request->getVar('maxattempts'));
                $extcoupon['status'] = trim($this->request->getVar('couponstatus'));

                $coupon = $this->enrollmentcouponmodel->updateCoupon($extcoupon, $extcoupon['id']);
            }

			
			if ( empty($coupon) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_ENROLLED_COUPON_UPDATED, $coupon), 200);
            
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
                'userid' => [
                    'label'  => 'User Id',
                    'rules'  => 'required'
				],
            ]);
        } elseif ( $type == 'update_coupon' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'couponcode' => [
                    'label'  => 'Coupon Code',
                    'rules'  => 'required'
                ],
                'maxattempts' => [
                    'label'  => 'Maximum Attempts',
                    'rules'  => 'required'
                ],
                'couponstatus' => [
                    'label'  => 'Coupon Status',
                    'rules'  => 'required'
                ]
            ]);
        } elseif ( $type == 'accept_coupon' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'couponcode' => [
                    'label'  => 'Coupon Code',
                    'rules'  => 'required'
                ]
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