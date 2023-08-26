<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Linkedprofile;

use App\Controllers\Core\AuthController;
use App\Models\App\Linkedprofile\LinkedprofileModel;
use App\Models\Core\UserModel;

class LinkedprofileController extends AuthController
{
    protected $linkedprofilemodel;

    public function __construct() {
        parent::__construct();
        $this->linkedprofilemodel = new LinkedprofileModel();
    }

    public function get($userid=0)
    {
        try {
            if ($userid == 0) {
                $userid = $this->request->getVar('userid');
                
                if ( !isset($userid) ) {
                    return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
                }
    
                return $this->respond($this->successResponse(200, "", $this->linkedprofilemodel->getLinkedProfiles($userid)), 200);
            }

            return $this->linkedprofilemodel->getLinkedProfiles($userid);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getUsersForLink()
    {
        try {
            $userid = $this->request->getVar('userid');

            if ( !isset($userid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $usermodel = new UserModel();
            $role = $usermodel->getUser($userid)->rolename;

            if ($role == 'Trainer') {
                return $this->respond($this->successResponse(200, "", $this->linkedprofilemodel->getProfilesToLink($userid, ['Student', 'Parent']), 200));
            } else if ($role == 'Parent') {
                return $this->respond($this->successResponse(200, "", $this->linkedprofilemodel->getProfilesToLink($userid, ['Student']), 200));
            } else if ($role == 'Student') {
                return $this->respond($this->successResponse(200, "", $this->linkedprofilemodel->getProfilesToLink($userid, ['Parent']), 200));
            } else {
                return $this->respond($this->successResponse(200, "", $this->linkedprofilemodel->getProfilesToLink($userid, ['Trainer']), 200));
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
        
            $userid = trim($this->request->getVar('userid'));

			$link = [
                'userid'=> trim($this->request->getVar('linkedprofileid')), 
                'linkedprofileid'=> $userid
            ];
            

			if ( !$this->linkedprofilemodel->saveLinkedProfile($link) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $link = [
                'userid'=> $userid, 
                'linkedprofileid'=> trim($this->request->getVar('linkedprofileid'))
            ];
            

			if ( !$this->linkedprofilemodel->saveLinkedProfile($link) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}


            $lastLinkID = $this->linkedprofilemodel->getInsertID();
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LINK_CREATED, $this->linkedprofilemodel->getLinkedProfile($lastLinkID)), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    public function delete()
	{
		$this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $link = $this->linkedprofilemodel->getLinkedProfile($id);

            if ( empty($link) ) {
                return $this->respond($this->errorResponse(404,"Link cannot be found."), 404);
            }

			if ( !$this->linkedprofilemodel->delete(['id'=>$link[0]->id]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}
          
            $relatedlink = $this->linkedprofilemodel->getLinkedRelatedProfile($link[0]->linkedprofileid, $link[0]->userid);

            if ( !empty($relatedlink) ) {
                $this->linkedprofilemodel->delete(['id'=>$relatedlink[0]->id]);
            }

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_LINK_DELETED), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
	}

    private function setValidationRules($type='')
    {
        if ( $type == 'save' ) {
            $this->validation->setRules([
                'userid' => [
                    'label'  => 'User',
                    'rules'  => 'required'
                ],
                'linkedprofileid' => [
                    'label'  => 'Linked Profile',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'update' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Linked Profile ID',
                    'rules'  => 'required'
                ],
                'userid' => [
                    'label'  => 'User',
                    'rules'  => 'required'
                ],
                'linkedprofileid' => [
                    'label'  => 'Linked Profile',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Linked Profile ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }  

}