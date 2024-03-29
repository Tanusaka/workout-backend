<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\App\Courses\CourseController;

class LessonController extends CourseController
{
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $section = $this->sectionmodel->getSection($id);

            if ( empty($section) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lessons = $this->lessonmodel->getLessons($id, 'A');

            return $this->respond($this->successResponse(200, "", $lessons), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lesson = $this->lessonmodel->getLesson($id);

            if ( empty($lesson) ) {
                return $this->respond($this->errorResponse(404,"Lesson cannot be found."), 404);
            }

            $section = $this->sectionmodel->getSection($lesson['sectionid']);

            $paymentInfo = $this->getCoursePaymentInfo($section['courseid']);

            if(!$paymentInfo['paymentrequired']) {
                return $this->respond($this->successResponse(200, "", $lesson), 200);
            } else {
                return $this->respond($this->errorResponse(402,"Payment Required.",
                ['paymentinfo'=>$paymentInfo]), 402);
            }

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getPrevious()
    {
        try {
            $courseid = $this->request->getVar('courseid');
            $currentLessonID = $this->request->getVar('currentid');

            if ( !isset($currentLessonID)) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $currentLesson = $this->lessonmodel->getLesson($currentLessonID);

            if ( empty($currentLesson) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lesson = $this->lessonmodel->getPreviousLesson($courseid, $currentLesson['lessonorder']-1);

            if ( empty($lesson) ) {
                return $this->respond($this->errorResponse(404,"There is no previous lesson."), 404);
            }

            $paymentInfo = $this->getCoursePaymentInfo($lesson['courseid']);

            if(!$paymentInfo['paymentrequired']) {
                return $this->respond($this->successResponse(200, "", $lesson), 200);
            } else {
                return $this->respond($this->errorResponse(402,"Payment Required.",
                ['paymentinfo'=>$paymentInfo]), 402);
            }

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getNext()
    {
        try {
            $courseid = $this->request->getVar('courseid');
            $currentLessonID = $this->request->getVar('currentid');

            if ( !isset($currentLessonID)) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $currentLesson = $this->lessonmodel->getLesson($currentLessonID);

            if ( empty($currentLesson) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $lesson = $this->lessonmodel->getNextLesson($courseid, $currentLesson['lessonorder']+1);

            if ( empty($lesson) ) {
                return $this->respond($this->errorResponse(404,"There is no next lesson."), 404);
            }

            $paymentInfo = $this->getCoursePaymentInfo($lesson['courseid']);

            if(!$paymentInfo['paymentrequired']) {
                return $this->respond($this->successResponse(200, "", $lesson), 200);
            } else {
                return $this->respond($this->errorResponse(402,"Payment Required.",
                ['paymentinfo'=>$paymentInfo]), 402);
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
        
            $courseid = trim($this->request->getVar('courseid'));
            $sectionid = trim($this->request->getVar('sectionid'));
            
			$lesson = [
                'courseid'=> $courseid, 
                'sectionid'=> $sectionid, 
                'lessonname'=> trim($this->request->getVar('lessonname')),
                'lessonduration'=> trim($this->request->getVar('lessonduration')),
				'lessondescription'=> trim($this->request->getVar('lessondescription')),
                'lessonmediaid'=> trim($this->request->getVar('lessonmediaid')),
                'lessonorder'=> 0,
			];

			if ( !$this->lessonmodel->saveLesson($lesson) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_CREATED,
            ['lesson'=>$this->lessonmodel->getLesson($this->lessonmodel->getInsertID())]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
	{
		$this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $lessonid = trim($this->request->getVar('lessonid'));

            $extlesson = $this->lessonmodel->getLesson($lessonid);

            if ( is_null($extlesson) ) {
                return $this->respond($this->errorResponse(404,"Lesson cannot be found."), 404);
            }

			$lesson = [
                'lessonname'=> trim($this->request->getVar('lessonname')),
                'lessonduration'=> trim($this->request->getVar('lessonduration')),
				'lessondescription'=> trim($this->request->getVar('lessondescription'))
			];

            $lessonmediaid = trim($this->request->getVar('lessonmediaid'));
            if ($lessonmediaid!="") {
                $lesson['lessonmediaid'] = $lessonmediaid;
            }

			if ( !$this->lessonmodel->updateLesson($lesson, $lessonid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_UPDATED,
            ['lesson'=>$this->lessonmodel->getLesson($lessonid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function updateOrder()
	{
		$this->setValidationRules('update_order');

        if ( $this->isValid() ) {           
        
            $orderList = $this->request->getVar('orderList');

            if ( empty($orderList) ) {
                return $this->respond($this->errorResponse(404,"Invalid Order List."), 400);
            }

            foreach ($orderList as $index => $lessonid) {
                $this->lessonmodel->updateLesson( ['lessonorder' => $index], $lessonid);
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

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LESSON_DELETED,
            ['lesson'=>$lesson]), 200);
        
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
                'sectionid' => [
                    'label'  => 'Section',
                    'rules'  => 'required'
                ],
                'lessonname' => [
                    'label'  => 'Lesson Name',
                    'rules'  => 'required|is_unique[course_lessons.lessonname]'
				],
                'lessonduration' => [
                    'label'  => 'Lesson Duration',
                    'rules'  => 'required'
                ],
                'lessondescription' => [
                    'label'  => 'Lesson Description',
                    'rules'  => 'required'
                ],
                'lessonmediaid' => [
                    'label'  => 'Lesson Media File',
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
                'lessonduration' => [
                    'label'  => 'Lesson Duration',
                    'rules'  => 'required'
                ],
                'lessondescription' => [
                    'label'  => 'Lesson Description',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'update_order' ) {
            $this->validation->setRules([
                'orderList' => [
                    'label'  => 'Order List',
                    'rules'  => 'required'
                ]
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