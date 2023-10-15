<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\LessonModel;
use App\Models\App\Courses\CourseModel;
use App\Models\App\Courses\SectionModel;
use App\Models\App\Courses\CoursePaymentsModel;
use App\Models\App\Courses\CourseSubscriptionsModel;

class LessonController extends AuthController
{
    protected $lessonmodel;
    protected $sectionmodel;
    protected $courseModel;
    protected $coursePaymentsModel;
    protected $courseSubscriptionModel;

    public function __construct() {
        parent::__construct();
        $this->lessonmodel = new LessonModel();
        $this->sectionmodel = new SectionModel();
        $this->courseModel = new CourseModel();
        $this->coursePaymentsModel = new CoursePaymentsModel();
        $this->courseSubscriptionModel = new CourseSubscriptionsModel();
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }
            $user_id = $this->getAuthID();

            if($this->access($user_id, $id)){
                $lesson = $this->lessonmodel->getLesson($id);

                if ( is_null($lesson) ) {
                    return $this->respond($this->errorResponse(404,"Lesson cannot be found."), 404);
                }

                return $this->respond($this->successResponse(200, "", $lesson), 200);
            } else {
                return $this->respond($this->errorResponse(403,"Access denied."), 403);
            }

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
				//'lessonmediapath'=> trim($this->request->getVar('lessonmediapath')),
                'lessondescription'=> trim($this->request->getVar('lessondescription')),
                //'lessonduration'=> trim($this->request->getVar('lessonduration')),
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

    public function access($user_id, $lessonid)
    {
        try {
            
            $lesson = $this->lessonmodel->getLesson($lessonid);
            dd($this->$sectionmodel);
            $section = $this->$sectionmodel->getSection($lesson['sectionid']);
            $course = $this->$courseModel->getCourse($section['courseid'], 'COURSE_ONLY');
            
            $user_id = $this->getAuthID();
            
        
            log_message('error', '[ERROR] {exception}', ['exception' => $e, 'course' => $course]);
            if($course['priceplan']=="Free" || $course['price']==0){
                return true;
            } else if ($course['priceplan']=="OneTime") {
                $coursePayment = $this->$coursePaymentsModel->getLastCoursePaymentByUser($user_id, $course['courseid']);
                if(!isset($coursePayment))
                    return false;
                return true;
            } else if ($course['priceplan']=="Montly") {
                $coursePayment = $this->$coursePaymentsModel->getLastCoursePaymentByUser($user_id, $course['courseid']);
                if(!isset($coursePayment)){
                    return false;
                } else {
                    helper('date');

                    $currentDateTime = new DateTime();
                    $anotherDateTime = new DateTime($coursePayment['CreatedAt']);

                    $interval = $currentDateTime->diff($anotherDateTime);

                    $days = $interval->d;
                    if($days > 31){
                        return false;                    
                    } else {
                        return true;
                    }
                }
                
            } else if ($course['priceplan']=="Yearly") {
                $coursePayment = $this->$coursePaymentsModel->getLastCoursePaymentByUser($user_id, $course['courseid']);
                if(!isset($coursePayment)){
                    return false;
                } else {
                    helper('date');

                    $currentDateTime = new DateTime();
                    $anotherDateTime = new DateTime($coursePayment['CreatedAt']);

                    $interval = $currentDateTime->diff($anotherDateTime);

                    $days = $interval->d;
                    if($days > 365){
                        return false;                   
                    } else {
                        return true;
                    }
                }
            }

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            //return false;
        }
    }
}