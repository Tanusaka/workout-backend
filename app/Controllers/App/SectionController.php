<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App;

use App\Controllers\Core\AuthController;
use App\Models\App\SectionModel;

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
                return $this->fail("Invalid Request", 400);
            }

            $section = $this->sectionmodel->getSection($id);

            if ( is_null($section) ) {
                return $this->failNotFound("Section cannot be found.");
            }

            return $this->respond($section, 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->failServerError(API_MSG_ERROR_ISE);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$section = [
				'courseid'=> trim($this->request->getVar('courseid')), 
				'title'=> trim($this->request->getVar('title')),
			];

			if ( !$this->sectionmodel->save_section($section) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_SECTION_CREATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
	}

    public function update()
	{
		$this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $sectionid = trim($this->request->getVar('sectionid'));

            $section = $this->sectionmodel->getSection($sectionid);

            if ( is_null($section) ) {
                return $this->failNotFound("Section cannot be found.");
            }

			$section = [
				'courseid'=> trim($this->request->getVar('courseid')), 
				'title'=> trim($this->request->getVar('title')),
                'status'=> trim($this->request->getVar('status')),
			];

			if ( !$this->sectionmodel->update_section($section, $sectionid) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_SECTION_UPDATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
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
                'title' => [
                    'label'  => 'Section Title',
                    'rules'  => 'required|is_unique[sections.title]'
				]
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'sectionid' => [
                    'label'  => 'Section ID',
                    'rules'  => 'required'
                ],
                'courseid' => [
                    'label'  => 'Course',
                    'rules'  => 'required'
                ],
                'title' => [
                    'label'  => 'Section Title',
                    'rules'  => 'required|is_unique[sections.title,id,{sectionid}]'
				]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }   
}