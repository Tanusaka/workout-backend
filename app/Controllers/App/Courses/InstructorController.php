<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\InstructorModel;

class InstructorController extends AuthController
{
    protected $instructormodel;

    public function __construct() {
        parent::__construct();
        $this->instructormodel = new InstructorModel();
    }

    public function get()
    {
        try {
            $courseid = $this->request->getVar('courseid');

            if ( !isset($courseid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $instructors = $this->instructormodel->getInstructors($courseid);
            
            return $this->respond($this->successResponse(200, "", $instructors), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getTrainersForLink()
    {
        try {
            $courseid = $this->request->getVar('courseid');

            if ( !isset($courseid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $res = [
                "results" => $this->instructormodel->getInstructorsToLink($courseid)
            ];

            return $this->respond($res);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save() {
        $this->setValidationRules('save');

        if ( $this->isValid() ) {           

			$instructor = [
                'courseid'=> trim($this->request->getVar('courseid')), 
                'userid'=> trim($this->request->getVar('userid')), 
                'type'=> trim($this->request->getVar('type')), 
            ];
            
			if ( !$this->instructormodel->saveInstructor($instructor) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $lastLinkID = $this->instructormodel->getInsertID();
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_INSTRUCTOR_CREATED, $this->instructormodel->getInstructor($lastLinkID)), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $instructor = $this->instructormodel->getInstructor($id);

            if ( empty($instructor) ) {
                return $this->respond($this->errorResponse(404,"Instructor cannot be found."), 404);
            }

			if ( !$this->instructormodel->delete(['id'=>$instructor[0]->id]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_INSTRUCTOR_DELETED), 200);
        
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
                'userid' => [
                    'label'  => 'Instructor',
                    'rules'  => 'required'
				],
                'type' => [
                    'label'  => 'Instructor Type',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Instructor ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    } 


}