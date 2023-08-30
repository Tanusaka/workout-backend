<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\CourseModel;
use App\Models\App\Courses\SectionModel;
use App\Models\App\Courses\LessonModel;
use App\Models\App\Courses\InstructorModel;
use App\Models\App\Courses\ReviewModel;
use App\Models\App\Courses\FollowerModel;

class CourseController extends AuthController
{
    protected $coursemodel;

    public function __construct() {
        parent::__construct();
        $this->coursemodel = new CourseModel();
    }

    public function index()
    {
        return $this->respond($this->successResponse(200, "", $this->coursemodel->getCourses()), 200);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $course = $this->coursemodel->getCourse($id);

            if ( is_null($course) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $course), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$course = [
                'tenantid'=> 1, //get this from token email
				'coursetype'=> trim($this->request->getVar('coursetype')), 
				'coursename'=> trim($this->request->getVar('coursename')),
                'courseintro'=> trim($this->request->getVar('courseintro')),
                'coursedescription'=> trim($this->request->getVar('coursedescription')),
                'coursemediapath'=> trim($this->request->getVar('coursemediapath')),
			];

			if ( !$this->coursemodel->save_course($course) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_CREATED, ['id'=>$this->coursemodel->getInsertID()]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
    {
        $this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $courseid = trim($this->request->getVar('courseid'));
			
            $extcourse = $this->coursemodel->getCourse($courseid, 'COURSE_ONLY');

            if ( empty($extcourse) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

            $course = [
				'coursetype'=> trim($this->request->getVar('coursetype')), 
				'coursename'=> trim($this->request->getVar('coursename')),
                'courseintro'=> trim($this->request->getVar('courseintro')),
                'coursedescription'=> trim($this->request->getVar('coursedescription')),
                //'coursemediapath' => trim($this->request->getVar('coursemediapath'))
			];

			if ( !$this->coursemodel->update_course($course, $courseid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_UPDATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $course = $this->coursemodel->getCourse($id);

            if ( empty($course) ) {
                return $this->respond($this->errorResponse(404,"Course cannot be found."), 404);
            }

			if ( !$this->coursemodel->delete(['id'=>$course['id']]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $sectionmodel = new SectionModel();
            $lessonmodel = new LessonModel();
            $instructormodel = new InstructorModel();
            $reviewmodel = new ReviewModel();
            $followermodel = new FollowerModel();

            #delete all contents
            $sections = $sectionmodel->getSections($course['id']);

            foreach ($sections as $section) {
                if ( !$lessonmodel->deleteLessonsBySection($section['id']) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }

                if ( !$sectionmodel->delete(['id'=>$section['id']]) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }
            }

            #delete all instructors
            $instructors = $instructormodel->getInstructors($course['id']);
            foreach ($instructors as $instructor) {
                if ( !$instructormodel->delete(['id'=>$instructor['id']]) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }
            }

            #delete all reviews
            $reviews = $reviewmodel->getReviews($course['id']);
            foreach ($reviews as $review) {
                if ( !$reviewmodel->delete(['id'=>$review['id']]) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }
            }

            #delete all followers
            $followers = $followermodel->getFollowers($course['id']);
            foreach ($followers as $follower) {
                if ( !$followermodel->delete(['id'=>$follower['id']]) ) {
                    return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
                }
            }


            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_COURSE_DELETED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'coursemediapath' => [
                    'label'  => 'Course Cover Image',
                    'rules'  => 'required'
                ],
                'coursename' => [
                    'label'  => 'Course Name',
                    'rules'  => 'required|is_unique[courses.coursename]'
				],
                'courseintro' => [
                    'label'  => 'Course Intro',
                    'rules'  => 'required'
				],
                'coursetype' => [
                    'label'  => 'Course Type',
                    'rules'  => 'required'
                ],
                'coursedescription' => [
                    'label'  => 'Course Description',
                    'rules'  => 'required'
                ],
                
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'courseid' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ],
                'coursename' => [
                    'label'  => 'Course Name',
                    'rules'  => 'required'
				],
                'courseintro' => [
                    'label'  => 'Course Intro',
                    'rules'  => 'required'
				],
                'coursetype' => [
                    'label'  => 'Course Type',
                    'rules'  => 'required'
                ],
                'coursedescription' => [
                    'label'  => 'Course Description',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Course ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}