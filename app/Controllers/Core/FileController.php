<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\Core;

use App\Controllers\Core\AuthController;
use App\Models\Core\FileModel;

class FileController extends AuthController
{
    protected $filemodel;

    public function __construct() {
        parent::__construct();
        $this->filemodel = new FileModel();
        $this->filemodel->currentuser = $this->getAuthID();
    }

    public function index()
    {
        $filters = [
            'tenantid' => '1',
            'rootdir' => 'public',
            'type' => '',
            'ext' => '',
            'download' => '',
            'accesslevel' => '',
            'status' => '',
            'createdby' => $this->getAuthID()
        ];

        return $this->respond($this->successResponse(200, "", $this->filemodel->getFiles($filters)), 200);
    }

    public function get()
    {
        try {
            $id = $this->request->getVar('id');

            if ( !isset($id) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $file = $this->filemodel->getFile($id);

            if ($file['accesslevel'] == 'PRIVATE' && $file['createdby'] != $this->getAuthID()) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            if ( is_null($file) ) {
                return $this->respond($this->errorResponse(404,"File cannot be found."), 404);
            }

            return $this->respond($this->successResponse(200, "", $file), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save()
	{
		$this->setValidationRules('save');

        if ( $this->isValid() ) {           
        
			$file = [
                'tenantid'=> 1, //get this from token email
                'rootdir' => trim($this->request->getVar('rootdir')),
				'path'=> trim($this->request->getVar('path')), 
				'name'=> trim($this->request->getVar('name')),
                'type'=> trim($this->request->getVar('type')),
                'ext' => trim($this->request->getVar('ext')),
                'size'=> trim($this->request->getVar('size'))
                // 'displayname'=> trim($this->request->getVar('displayname')),
                // 'filedescription'=> trim($this->request->getVar('filedescription')),
                // 'sharelink' => trim($this->request->getVar('sharelink')),
                // 'download'=> trim($this->request->getVar('download')),
                // 'accesslevel'=> trim($this->request->getVar('accesslevel'))
			];

            $fileid = $this->filemodel->saveFile($file);

			if ( $fileid == 0 ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_FILE_CREATED, ['file'=>$this->filemodel->getFile($fileid)]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function delete() 
    {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {    
            
            $files = trim($this->request->getVar('files'));

            $files = explode ("-", $files); $links = [];
            
            foreach ($files as $id) {
				
				$file = $this->filemodel->find($id); 
				
				if (!empty($file) && $this->filemodel->delete([ 'id' => $file['id'] ])) {
                    array_push($links, $file['path'].$file['name']);
				}
			}

			if ( empty($links) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_FILE_DELETED, ['links' => $links]), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'rootdir' => [
                    'label'  => 'Root Folder Name',
                    'rules'  => 'required'
                ],
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
                ]
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'files' => [
                    'label'  => 'Files',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }   

}