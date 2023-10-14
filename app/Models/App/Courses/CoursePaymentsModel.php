<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class CoursePaymentsModel extends Model
{

  protected $table      = 'course_payments';
  protected $primaryKey = 'PaymentID';

  protected $protectFields    = false;

  // Dates
  protected $useTimestamps        = true;
  protected $dateFormat           = 'datetime';
  protected $createdField         = 'CreatedAt';
  protected $updatedField         = 'UpdatedAt';
  #protected $deletedField         = 'deleted_at';

  // Callbacks
  protected $allowCallbacks       = true;
  protected $beforeInsert         = ['setStatus'];
  // protected $afterInsert          = [];
  // protected $beforeUpdate         = [];
  // protected $afterUpdate          = [];
  // protected $beforeFind           = [];
  // protected $afterFind            = [];
  // protected $beforeDelete         = [];
  // protected $afterDelete          = [];


  protected function setStatus(array $data)
  {   
    $data['data']['Status'] = 'A';
    return $data;
  }

  public function getCoursesPayments($userId=0)
  {
    $selectColumns = ['PaymentID',	'UserID',	'Amount',	'PaymentDate',	'PaymentMethod',	'PaymentReference',	'subscritionid',	'CourseID',	'CreatedAt',	'CreatedBy',	'UpdatedAt',	'UpdatedBy'];

    return $this->select($selectColumns)->where('UserID', $userId)->findAll();
    
  }

  public function getCoursesPayment($userId=0, $courseId=0)
  {
    try {
      $selectColumns = ['PaymentID',	'UserID',	'Amount',	'PaymentDate',	'PaymentMethod',	'PaymentReference',	'subscritionid',	'CourseID',	'CreatedAt',	'CreatedBy',	'UpdatedAt',	'UpdatedBy'];
  
      $coursePayment = $this->select($selectColumns)->where('UserID', $userId)->where('CourseID', $courseId)->first();

      if ( !isset($coursePayment) ) {
        return null;
      }      

      return $coursePayment;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function saveCoursePayment($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function deleteCoursePayment($coursePaymentId=[])
  {
    
    if ( is_null($coursePaymentId) ) { return false; }

    return $this->where('PaymentID', $coursePaymentId)->delete();
  }

}