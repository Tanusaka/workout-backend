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
            $user_id = $this->getAuthID();
            $lessonid = $this->request->getVar('lessonid');            

            if ( !isset($lessonid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lessonDuration = $this->lessondurationmodel->getLessonDuration($user_id, $lessonid);

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
				'lessonid'=> trim($this->request->getVar('lessonid')),
				'userid'=> $user_id,
			];

			$lessonDurationExist = $this->lessondurationmodel->getLessonDuration($user_id, $lessonDuration['lessonid']);

		    	if ( is_null($lessonDurationExist) ) {                
                    $completion = [
                        'completable'=> FALSE, 
                    ];
                    if ( !$this->lessondurationmodel->saveLessonDuration($lessonDuration) ) {
                        return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                    } else {
                        return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_CREATED, $completion), 200);	
                    }				
		    	} else {
                    if ( !$this->lessondurationmodel->updateLessonDuration($lessonDurationExist, $lessonDurationExist['id']) ) {
                        return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                    } else {                        
                        $completion = [
                            'completable'=> FALSE, 
                        ];
                        $lessonDurationValue = $this->lessonmodel->getLesson($lessonDuration['lessonid'])['lessonduration'];
                        if(is_null($lessonDurationValue) || $lessonDurationValue < $lessonDurationExist['duration']){     
                            $completion['completable'] = TRUE;
                            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_UPDATED, $completion), 200);	
                        } else {                            
                            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_UPDATED, $completion), 200);	
                        }
				}	
			}				            	
	        
		} else {
	            return $this->respond($this->errorResponse(400,$this->errors), 400);
	        }
	}

    public function update()
	{	
	    if ( $this->isValid() ) {    
			
			$user_id = $this->getAuthID();
		        
			$lessonDuration = [
				'lessonid'=> trim($this->request->getVar('lessonid')),
				'userid'=> $user_id,
                'completed' => trim($this->request->getVar('completed')),
			];

			$lessonDurationExist = $this->lessondurationmodel->getLessonDuration($user_id, $lessonDuration['lessonid']);

		    	if ( is_null($lessonDurationExist) ) {
                    return $this->respond($this->errorResponse(404,"Lesson Duration info cannot be found."), 404);		
		    	} else {
                    $lessonDurationValue = $this->lessonmodel->getLesson($lessonDuration['lessonid'])['lessonduration'];
                    if($lessonDuration['completed']==1) {
                        if(is_null($lessonDurationValue) || $lessonDurationValue < $lessonDurationExist['duration']){     
                            $this->lessondurationmodel->updateLessonDurationCompletion($lessonDuration, $lessonDurationExist['id']);
                            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_UPDATED), 200);	
                        }
                    } else if($lessonDuration['completed']==0){
                        $this->lessondurationmodel->updateLessonDurationCompletion($lessonDuration, $lessonDurationExist['id']);
                        return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DURATION_UPDATED), 200);
                    } else {
                        return $this->respond($this->errorResponse(400,$this->errors), 400);
                    }
				}	
			}				            	
	        
		else {
	            return $this->respond($this->errorResponse(400,"Data Error."), 400);
	        }
	}
       
}
