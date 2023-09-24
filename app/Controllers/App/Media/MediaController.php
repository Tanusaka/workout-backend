<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Media;

use App\Controllers\Core\AuthController;
use App\Models\App\Media\MediaModel;

class MediaController extends AuthController
{
    protected $mediamodel;

    public function __construct() {
        parent::__construct();
        $this->mediamodel = new MediaModel();
    }

    public function index()
    {
        $filters = [
            'tenantid' => 1,
            'type' => '',
            'ext' => '',
            'size' => '',
            'status' => '',
            'createdby' => ''
        ];
        return $this->respond($this->successResponse(200, "", $this->mediamodel->getMedia($filters)), 200);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $media = $this->mediamodel->find($id);

            if ( is_null($media) ) {
                return $this->respond($this->errorResponse(404,"Media file cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $media), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$media = [
                'tenantid'=> 1, //get this from token email
				'path'=> trim($this->request->getVar('path')), 
				'name'=> trim($this->request->getVar('name')),
                'type'=> trim($this->request->getVar('type')),
                'ext' => trim($this->request->getVar('ext')),
                'size'=> trim($this->request->getVar('size'))
			];

			if ( !$this->mediamodel->saveMedia($media) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_MEDIA_CREATED, ['id'=>$this->mediamodel->getInsertID()]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $media = $this->mediamodel->find($id);

            if ( empty($media) ) {
                return $this->respond($this->errorResponse(404,"Media file cannot be found."), 404);
            }

			if ( !$this->mediamodel->delete($id) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_MEDIA_DELETED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'path' => [
                    'label'  => 'File Path',
                    'rules'  => 'required'
                ],
                'name' => [
                    'label'  => 'File Name',
                    'rules'  => 'required'
				],
                'type' => [
                    'label'  => 'File Type',
                    'rules'  => 'required'
				],
                'ext' => [
                    'label'  => 'File Extension',
                    'rules'  => 'required'
                ],
                'size' => [
                    'label'  => 'File Size',
                    'rules'  => 'required'
                ],
                
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'File ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }   

}