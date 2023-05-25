<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App;

use App\Controllers\Core\AuthController;
use App\Models\App\CourseModel;

class CourseController extends AuthController
{
    protected $coursemodel;

    public function __construct() {
        parent::__construct();
        $this->coursemodel = new CourseModel();
    }

    public function index()
    {
        return $this->respond($this->coursemodel->getCourses(), 200);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->fail("Invalid Request", 400);
            }

            $course = $this->coursemodel->getCourse($id);

            if ( is_null($course) ) {
                return $this->failNotFound("Course cannot be found.");
            }

            return $this->respond($course, 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->failServerError(API_MSG_ERROR_ISE);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$course = [
                'prid'=> trim($this->request->getVar('prid')), 
				'type'=> trim($this->request->getVar('type')), 
				'title'=> trim($this->request->getVar('title')),
                'subtitle'=> trim($this->request->getVar('subtitle')),
                'level'=> trim($this->request->getVar('level')),
                'description'=> trim($this->request->getVar('description')),
                'covermediatype'=> trim($this->request->getVar('covermediatype')),
                'covermedia'=> trim($this->request->getVar('covermedia')),
			];

			if ( !$this->coursemodel->save_course($course) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_COURSE_CREATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
	}

    public function update()
    {
        $this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $courseid = trim($this->request->getVar('courseid'));
			
            $course = $this->coursemodel->getCourse($courseid, 'COURSE_ONLY');

            if ( is_null($course) ) {
                return $this->failNotFound("Course cannot be found.");
            }

            $course = [
				'type'=> trim($this->request->getVar('type')), 
				'title'=> trim($this->request->getVar('title')),
                'subtitle'=> trim($this->request->getVar('subtitle')),
                'level'=> trim($this->request->getVar('level')),
                'description'=> trim($this->request->getVar('description')),
                'covermediatype'=> trim($this->request->getVar('covermediatype')),
                'covermedia'=> trim($this->request->getVar('covermedia')),
                'status'=> trim($this->request->getVar('status')),
			];

			if ( !$this->coursemodel->update_course($course, $courseid) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_COURSE_UPDATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'prid' => [
                    'label'  => 'Primary Domain ID',
                    'rules'  => 'required'
                ],
                'type' => [
                    'label'  => 'Course Type',
                    'rules'  => 'required'
                ],
                'title' => [
                    'label'  => 'Course Title',
                    'rules'  => 'required|is_unique[courses.title]'
				],
				'level' => [
					'label'  => 'Course Level',
					'rules'  => 'required'
				  ]
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'type' => [
                    'label'  => 'Course Type',
                    'rules'  => 'required'
                ],
                'title' => [
                    'label'  => 'Course Title',
                    'rules'  => 'required'
				],
				'level' => [
					'label'  => 'Course Level',
					'rules'  => 'required'
				]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}