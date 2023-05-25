<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App;

use App\Controllers\Core\AuthController;
use App\Models\App\ContentModel;

class ContentController extends AuthController
{
    protected $contentmodel;

    public function __construct() {
        parent::__construct();
        $this->contentmodel = new ContentModel();
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->fail("Invalid Request", 400);
            }

            $content = $this->contentmodel->getContent($id);

            if ( is_null($content) ) {
                return $this->failNotFound("Content cannot be found.");
            }

            return $this->respond($content, 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->failServerError(API_MSG_ERROR_ISE);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$content = [
                'sectionid'=> trim($this->request->getVar('sectionid')), 
                'type'=> trim($this->request->getVar('type')),
				'title'=> trim($this->request->getVar('title')),
                'contentmedia'=> trim($this->request->getVar('contentmedia')),
                'duration'=> trim($this->request->getVar('duration')),
			];

			if ( !$this->contentmodel->save_content($content) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_CONTENT_CREATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
        }
	}

    public function update()
	{
		$this->setValidationRules('update');

        if ( $this->isValid() ) {           
        
            $contentid = trim($this->request->getVar('contentid'));

            $content = $this->contentmodel->getContent($contentid);

            if ( is_null($content) ) {
                return $this->failNotFound("Content cannot be found.");
            }

			$content = [
                'sectionid'=> trim($this->request->getVar('sectionid')), 
                'type'=> trim($this->request->getVar('type')),
				'title'=> trim($this->request->getVar('title')),
                'contentmedia'=> trim($this->request->getVar('contentmedia')),
                'duration'=> trim($this->request->getVar('duration')),
                'status'=> trim($this->request->getVar('status')),
			];

			if ( !$this->contentmodel->update_content($content, $contentid) ) {
				return $this->failServerError(API_MSG_ERROR_ISE);
			}

			return $this->respond($this->getSuccessResponse(API_MSG_SUCCESS_CONTENT_UPDATED), 200);
        
		} else {
            return $this->failValidationErrors($this->errors);
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
                'type' => [
                    'label'  => 'Content Type',
                    'rules'  => 'required'
                ],
                'title' => [
                    'label'  => 'Content Title',
                    'rules'  => 'required|is_unique[contents.title]'
				],
                'contentmedia' => [
                    'label'  => 'Content Media',
                    'rules'  => 'required'
                ],
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'contentid' => [
                    'label'  => 'Content ID',
                    'rules'  => 'required'
                ],
                'sectionid' => [
                    'label'  => 'Section',
                    'rules'  => 'required'
                ],
                'type' => [
                    'label'  => 'Content Type',
                    'rules'  => 'required'
                ],
                'title' => [
                    'label'  => 'Content Title',
                    'rules'  => 'required|is_unique[contents.title,id,{contentid}]'
				],
                'contentmedia' => [
                    'label'  => 'Content Media',
                    'rules'  => 'required'
                ],
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }    
}