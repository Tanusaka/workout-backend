<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class ReviewModel extends Model
{

    protected $table      = 'course_reviews';
    protected $primaryKey = 'id';

    protected $protectFields    = false;

    // Dates
    protected $useTimestamps        = true;
    protected $dateFormat           = 'datetime';
    protected $createdField         = 'createdat';
    protected $updatedField         = 'updatedat';
    #protected $deletedField         = 'deleted_at';

    // Callbacks
    protected $allowCallbacks       = true;
    protected $beforeInsert         = ['setStatus'];
    protected $afterInsert          = [];
    protected $beforeUpdate         = [];
    protected $afterUpdate          = [];
    protected $beforeFind           = [];
    protected $afterFind            = [];
    protected $beforeDelete         = [];
    protected $afterDelete          = [];


    protected function setStatus(array $data)
    {   
        $data['data']['status'] = 'A';
        return $data;
    }

    public function getReviews($courseid=0) {
        return $this->db->table('course_reviews')->select(
        [
            'course_reviews.id',
            'course_reviews.userid',
            'course_reviews.review',
            'course_reviews.rating',
            '_users.firstname',
            '_users.lastname'
        ])
        ->join('_users', '_users.id = course_reviews.userid')
        ->where('course_reviews.courseid', $courseid)
        ->get()->getResultArray();
    }

    public function getReview($id=0) {
        return $this->db->table('course_reviews')->select(
        [
            'course_reviews.id',
            'course_reviews.userid',
            'course_reviews.review',
            'course_reviews.rating',
            '_users.firstname',
            '_users.lastname'
        ])
        ->join('_users', '_users.id = course_reviews.userid')
        ->where('course_reviews.id', $id)
        ->get()->getResult();
    }

    public function saveReview($data=[])
    {
        return is_null($data) ? false : ( $this->insert($data) ? true : false );
    }

    public function deleteReview($id=null)
    {
        return $this->where('id', $id)->delete();
    }

}