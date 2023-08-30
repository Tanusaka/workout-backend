<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\ReviewModel;

class ReviewController extends AuthController
{
    protected $reviewmodel;

    public function __construct() {
        parent::__construct();
        $this->reviewmodel = new ReviewModel();
    }

    public function get()
    {
        try {
            $courseid = $this->request->getVar('courseid');

            if ( !isset($courseid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $reviews = $this->reviewmodel->getReviews($courseid);
            
            return $this->respond($this->successResponse(200, "", $reviews), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save() {
        $this->setValidationRules('save');

        if ( $this->isValid() ) {           

			$review = [
                'courseid'=> trim($this->request->getVar('courseid')), 
                'userid'=> $this->getAuthID(), 
                'review'=> trim($this->request->getVar('review')), 
                'rating'=> trim($this->request->getVar('rating')), 
            ];
            
			if ( !$this->reviewmodel->saveReview($review) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $lastLinkID = $this->reviewmodel->getInsertID();
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_REVIEW_CREATED, $this->reviewmodel->getReview($lastLinkID)), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $review = $this->reviewmodel->getReview($id);

            if ( empty($review) ) {
                return $this->respond($this->errorResponse(404,"Review cannot be found."), 404);
            }

			if ( !$this->reviewmodel->delete(['id'=>$review[0]->id]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_REVIEW_DELETED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course',
                    'rules'  => 'required'
                ],
                'review' => [
                    'label'  => 'Review',
                    'rules'  => 'required'
				],
                'rating' => [
                    'label'  => 'Rating',
                    'rules'  => 'required'
				],
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Review ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    } 

}