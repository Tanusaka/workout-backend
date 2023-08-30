<?php
/**
 *
 * @author Samu
 */
namespace App\Controllers\App\Courses;

use App\Controllers\Core\AuthController;
use App\Models\App\Courses\FollowerModel;

class FollowerController extends AuthController
{
    protected $followermodel;

    public function __construct() {
        parent::__construct();
        $this->followermodel = new FollowerModel();
    }

    public function get()
    {
        try {
            $courseid = $this->request->getVar('courseid');

            if ( !isset($courseid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $followers = $this->followermodel->getFollowers($courseid);
            
            return $this->respond($this->successResponse(200, "", $followers), 200);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function getUsersForLink()
    {
        try {
            $courseid = $this->request->getVar('courseid');

            if ( !isset($courseid) ) {
                return $this->respond($this->errorResponse(400,"Invalid Request."), 400);
            }

            $res = [
                "results" => $this->followermodel->getFollowersToLink($courseid)
            ];

            return $this->respond($res);

        } catch (\Exception $e) {
            log_message('error', '[ERROR] {exception}', ['exception' => $e]);
            return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
        }
    }

    public function save() {
        $this->setValidationRules('save');

        if ( $this->isValid() ) {           

			$follower = [
                'courseid'=> trim($this->request->getVar('courseid')), 
                'userid'=> trim($this->request->getVar('userid')), 
                'type'=> trim($this->request->getVar('type')), 
            ];
            
			if ( !$this->followermodel->saveFollower($follower) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            $lastLinkID = $this->followermodel->getInsertID();
            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_FOLLOWER_CREATED, $this->followermodel->getFollower($lastLinkID)), 200);
        
		} else {
            return $this->respond($this->errorResponse(400,$this->errors), 400);
        }
    }

    public function delete() {
        $this->setValidationRules('delete');

        if ( $this->isValid() ) {           
        
            $id = trim($this->request->getVar('id'));

            $follower = $this->followermodel->getFollower($id);

            if ( empty($follower) ) {
                return $this->respond($this->errorResponse(404,"Follower cannot be found."), 404);
            }

			if ( !$this->followermodel->delete(['id'=>$follower[0]->id]) ) {
				return $this->respond($this->errorResponse(500,"Internal Server Error."), 500);
			}

            return $this->respond($this->successResponse(200, API_MSG_SUCCESS_FOLLOWER_DELETED), 200);
        
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
                'userid' => [
                    'label'  => 'Follower',
                    'rules'  => 'required'
				],
                'type' => [
                    'label'  => 'Follower Type',
                    'rules'  => 'required'
				]
            ]);
        } elseif ( $type == 'delete' ) {
            $this->validation->setRules([
                'id' => [
                    'label'  => 'Follower ID',
                    'rules'  => 'required'
                ]
            ]);
        } else {
            $this->validation->setRules([]);
        }
    }

}