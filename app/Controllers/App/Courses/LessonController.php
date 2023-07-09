<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\LessonModel;

class LessonController extends AuthController
{
    protected $lessonmodel;

    public function __construct() {
        parent::__construct();
        $this->lessonmodel = new LessonModel();
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lesson = $this->lessonmodel->getLesson($id);

            if ( is_null($lesson) ) {
                return $this->respond($this->errorResponse(404,"Lesson cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $lesson), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$lesson = [
                'sectionid'=> trim($this->request->getVar('sectionid')), 
                'lessonname'=> trim($this->request->getVar('lessonname')),
				'lessonmediapath'=> trim($this->request->getVar('lessonmediapath')),
                'lessondescription'=> trim($this->request->getVar('lessondescription')),
                'lessonduration'=> trim($this->request->getVar('lessonduration')),
			];

			if ( !$this->lessonmodel->saveLesson($lesson) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_CREATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
	{
		$this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $lessonid = trim($this->request->getVar('lessonid'));

            $lesson = $this->lessonmodel->getLesson($lessonid);

            if ( is_null($lesson) ) {
                return $this->respond($this->errorResponse(404,"Lesson cannot be found."), 404);
            }

			$lesson = [
                'lessonname'=> trim($this->request->getVar('lessonname')),
				'lessonmediapath'=> trim($this->request->getVar('lessonmediapath')),
                'lessondescription'=> trim($this->request->getVar('lessondescription')),
                'lessonduration'=> trim($this->request->getVar('lessonduration')),
			];

			if ( !$this->lessonmodel->updateLesson($lesson, $lessonid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_UPDATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'sectionid' => [
                    'label'  => 'Section',
                    'rules'  => 'required'
                ],
                'lessonname' => [
                    'label'  => 'Lesson Name',
                    'rules'  => 'required|is_unique[course_lessons.lessonname]'
				],
                'lessonmediapath' => [
                    'label'  => 'Lesson Video',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'lessonid' => [
                    'label'  => 'Lesson ID',
                    'rules'  => 'required'
                ],
                'lessonname' => [
                    'label'  => 'Lesson Name',
                    'rules'  => 'required|is_unique[course_lessons.lessonname,id,{lessonid}]'
				],
                'lessonmediapath' => [
                    'label'  => 'Lesson Video',
                    'rules'  => 'required'
                ],
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}