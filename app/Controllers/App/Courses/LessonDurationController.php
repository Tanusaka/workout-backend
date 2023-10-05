<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\LessonCompletionModel;

class LessonDurationController extends AuthController
{
    protected $lessondurationmodel;

    public function __construct() {
        parent::__construct();
        $this->lessondurationmodel = new LessonDurationModel();
    }

    public function get()
    {
        try {
            $courseid = $this->request->getVar('courseid');
            $sectionid = $this->request->getVar('sectionid');
            $lessonid = $this->request->getVar('lessonid');            

            if ( !isset($lessonid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lessonDuration = $this->lessondurationmodel->getLessonDuration($courseid, $sectionid, $lessonid);

            if ( is_null($lessonDuration) ) {
                return $this->respond($this->errorResponse(404,"Lesson info cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $lessonCompletion), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{	
	        if ( $this->isValid() ) {    
			
			$user_id = $this->getAuthID();
		        
			$lessonDuration = [
				'courseid'=> trim($this->request->getVar('courseid')), 
				'sectionid'=> trim($this->request->getVar('sectionid')),
				'lessonid'=> trim($this->request->getVar('lessonid')),
				'userid'=> $user_id,
			];
		
			if ( !$this->lessondurationmodel->saveLessonDuration($lessonDuration) ) {
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
				//'lessonmediapath'=> trim($this->request->getVar('lessonmediapath')),
                'lessondescription'=> trim($this->request->getVar('lessondescription')),
                //'lessonduration'=> trim($this->request->getVar('lessonduration')),
			];

			if ( !$this->lessonmodel->updateLesson($lesson, $lessonid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_UPDATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $lesson = $this->lessonmodel->getLesson($id);

            if ( empty($lesson) ) {
                return $this->respond($this->errorResponse(404,"Lesson cannot be found."), 404);
            }

			if ( !$this->lessonmodel->delete(['id'=>$lesson['id']]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DELETED), 200);
        
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
                // 'lessonmediapath' => [
                //     'label'  => 'Lesson Video',
                //     'rules'  => 'required'
                // ],
                // 'lessonduration' => [
                //     'label'  => 'Lesson Duration',
                //     'rules'  => 'required'
                // ],
                'lessondescription' => [
                    'label'  => 'Lesson Description',
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
                // 'lessonmediapath' => [
                //     'label'  => 'Lesson Video',
                //     'rules'  => 'required'
                // ],
                // 'lessonduration' => [
                //     'label'  => 'Lesson Duration',
                //     'rules'  => 'required'
                // ],
                'lessondescription' => [
                    'label'  => 'Lesson Description',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Lesson ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}
