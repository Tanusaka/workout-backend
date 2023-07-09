<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\SectionModel;

class SectionController extends AuthController
{
    protected $sectionmodel;

    public function __construct() {
        parent::__construct();
        $this->sectionmodel = new SectionModel();
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $section = $this->sectionmodel->getSection($id);

            if ( is_null($section) ) {
                return $this->respond($this->errorResponse(404,"Section cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $section), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$section = [
				'courseid'=> trim($this->request->getVar('courseid')), 
				'sectionname'=> trim($this->request->getVar('sectionname')),
			];

			if ( !$this->sectionmodel->save_section($section) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_SECTION_CREATED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function update()
	{
		$this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $sectionid = trim($this->request->getVar('sectionid'));

            $section = $this->sectionmodel->getSection($sectionid);

            if ( is_null($section) ) {
                return $this->respond($this->errorResponse(404,"Section cannot be found."), 404);
            }

			$section = [
				'sectionname'=> trim($this->request->getVar('sectionname')),
			];

			if ( !$this->sectionmodel->update_section($section, $sectionid) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_SECTION_UPDATED), 200);
        
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
                'sectionname' => [
                    'label'  => 'Section Name',
                    'rules'  => 'required|is_unique[course_sections.sectionname]'
				]
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'sectionid' => [
                    'label'  => 'Course Section ID',
                    'rules'  => 'required'
                ],
                'sectionname' => [
                    'label'  => 'Section Name',
                    'rules'  => 'required|is_unique[course_sections.sectionname,id,{sectionid}]'
				]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }   
}