<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class EnrollmentcouponModel extends Model
{

  protected $table      = 'course_enrollments_coupons';
  protected $primaryKey = 'id';

  protected $protectFields    = false;

  // Dates
  protected $useTimestamps        = true;
  protected $dateFormat           = 'datetime';
  protected $createdField         = 'createdat';
  protected $updatedField         = 'updatedat';
  #protected $deletedField        = 'deleted_at';

  // Callbacks
  protected $allowCallbacks       = true;
//   protected $beforeInsert         = [];
  // protected $afterInsert          = [];
  // protected $beforeUpdate         = [];
  // protected $afterUpdate          = [];
  // protected $beforeFind           = [];
  // protected $afterFind            = [];
  // protected $beforeDelete         = [];
  // protected $afterDelete          = [];


  public function getCoupon($id=0) {
    return $this->where('id', $id)->first();
  }

  public function getCouponByCourse($courseid=0) {
    return $this->where('courseid', $courseid)->get()->getRowArray();
  }

  public function saveCoupon($data=[])
  {
    return is_null($data) ? [] : ( $this->insert($data) ? $this->getCoupon($this->getInsertID()) : [] );
  }

  public function validateCoupon($courseid=0, $couponcode='') {

    $where = "courseid ='".$courseid."' AND couponcode ='".$couponcode."' AND status='A' AND currentattempts < maxattempts";

    $coupon = $this->where($where)->get()->getRowArray();

    if (!empty($coupon)) {
      $this->set('currentattempts', $coupon['currentattempts']+1);
      $this->where('id', $coupon['id'])->update();
      return true;
    }

    return false;

  }

  public function updateCoupon($data=[], $id=null)
  {
    if ( isset($data['couponcode']) ) { $this->set('couponcode', $data['couponcode']); }
    if ( isset($data['maxattempts']) ) { $this->set('maxattempts', $data['maxattempts']); }
    if ( isset($data['status']) ) { $this->set('status', $data['status']); }

    return is_null($data) ? [] : ( $this->where('id', $id)->update() ? $this->getCoupon($id) : [] );
  }

}