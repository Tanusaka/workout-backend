<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\LessonDurationModel;
use App\Models\App\Courses\LessonModel;

class LessonDurationController extends AuthController
{
    protected $lessondurationmodel;
    protected $lessonmodel;

    public function __construct() {
        parent::__construct();
        $this->lessondurationmodel = new LessonDurationModel();
        $this->lessonmodel = new LessonModel();
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
                return $this->respond($this->errorResponse(404,"Lesson Duration info cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $lessonDuration), 200);

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

			$lessonDurationExist = $this->lessondurationmodel->getLessonDuration($lessonDuration['courseid'], $lessonDuration['sectionid'], $lessonDuration['lessonid']);

		    	if ( is_null($lessonDurationExist) ) {
                    if ( !$this->lessondurationmodel->saveLessonDuration($lessonDuration) ) {
                        return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                    } else {
                        return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_CREATED), 200);	
                    }				
		    	} else {
                    if ( !$this->lessondurationmodel->updateLessonDuration($lessonDurationExist, $lessonDurationExist['id']) ) {
                        return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                    } else {                        

                        $lessonDurationValue = $this->lessonmodel->getLesson($lessonDuration['lessonid']);
                        return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_UPDATED, $lessonDurationValue), 200);	
				}	
			}				            	
	        
		} else {
	            return $this->respond($this->errorResponse(400,$this->errors), 400);
	        }
	}
       
}
