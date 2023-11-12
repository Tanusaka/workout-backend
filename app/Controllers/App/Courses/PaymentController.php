<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\CourseModel;
use App\Models\App\Courses\PaymentModel;

class PaymentController extends AuthController
{
    protected $paymentmodel;

    public function __construct() {
        parent::__construct();
        $this->paymentmodel = new PaymentModel();
    }

    // public function index()
    // {
    //     $user_id = $this->getAuthID();
    //     return $this->respond($this->successResponse(200, "", $this->paymentmodel->getCoursesPaymentsByUser($user_id), 200));
    // }

    // public function get()
    // {
    //     try {
    //         $user_id = $this->getAuthID();
    //         $course_id = $this->request->getVar('courseid');

    //         if ( !isset($course_id) ) {
    //             return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
    //         }

    //         $coursePayment = $this->paymentmodel->getCoursePaymentsByUser($user_id, $course_id);

    //         if ( is_null($coursePayment) ) {
    //             return $this->respond($this->errorResponse(404,"Course Payment cannot be found."), 404);
    //         }

    //         return $this->respond($this->successResponse(200, "", $coursePayment), 200);

    //     } catch (\Exception $e) {
    //         log_message('error', '[ERROR] {exception}', ['exception' => $e]);
    //         return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
    //     }
    // }

    // public function getLastCoursePaymentByUser()
    // {
    //     try {
    //         $user_id = $this->getAuthID();
    //         $course_id = $this->request->getVar('courseid');

    //         if ( !isset($course_id) ) {
    //             return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
    //         }

    //         $coursePayment = $this->paymentmodel->getLastCoursePaymentByUser($user_id, $course_id);

    //         if ( is_null($coursePayment) ) {
    //             return $this->respond($this->errorResponse(404,"No Course Payment found."), 404);
    //         }

    //         return $this->respond($this->successResponse(200, "", $coursePayment), 200);

    //     } catch (\Exception $e) {
    //         log_message('error', '[ERROR] {exception}', ['exception' => $e]);
    //         return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
    //     }
    // }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$payment = [
                'userid'=> $this->getAuthID(),
                'courseid'=> trim($this->request->getVar('courseid')),
                'subscriptionid' => trim($this->request->getVar('subscriptionid')),
                'orderreference' => trim($this->request->getVar('orderreference')),
                'amount' => trim($this->request->getVar('amount')),
                'currency'=> trim($this->request->getVar('currency')),
                'method' => trim($this->request->getVar('method')),
                'paidon' => trim($this->request->getVar('paidon')),
                'payerid' => trim($this->request->getVar('payerid')),
                'payername' => trim($this->request->getVar('payername')),
                'payeremail' => trim($this->request->getVar('payeremail')),
                'payeraddress' => trim($this->request->getVar('payeraddress')),
                'status' => trim($this->request->getVar('status'))
			];

			if ( !$this->paymentmodel->savePayment($payment) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_PAID), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
				],
                'orderreference' => [
                    'label'  => 'Order Reference',
                    'rules'  => 'required'
				],
                'amount' => [
                    'label'  => 'Amount',
                    'rules'  => 'required'
				],
                'currency' => [
                    'label'  => 'Currency',
                    'rules'  => 'required'
				],
                'status' => [
                    'label'  => 'Status',
                    'rules'  => 'required'
				],
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}