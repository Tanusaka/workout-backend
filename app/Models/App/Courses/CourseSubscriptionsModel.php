<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class CourseSubscriptionsModel extends Model
{

  protected $table      = 'course_subscriptions';
  protected $primaryKey = 'SubscriptionID';

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
    $data['data']['Status'] = '1';
    return $data;
  }

  public function getCoursesSubscriptions($userId=0)
  {
    $selectColumns = ['SubscriptionID',	'UserID',	'CourseID',	'SubscriptionReference',	'PaymentMethod',	'Reference',	'StartDate', 'EndDate',	'CreatedAt',	'CreatedBy',	'UpdatedAt',	'UpdatedBy'	];

    return $this->select($selectColumns)->where('UserID', $userId)->findAll();
    
  }

  public function getCourseSubscriptions($userId=0, $courseId=0)
  {
    $selectColumns = ['SubscriptionID',	'UserID',	'CourseID',	'SubscriptionReference',	'PaymentMethod',	'Reference',	'StartDate', 'EndDate',	'CreatedAt',	'CreatedBy',	'UpdatedAt',	'UpdatedBy'	];

    return $this->select($selectColumns)->where('UserID', $userId)->where('CourseID', $courseId)->findAll();
    
  }

  public function getCoursesSubscription($subscriptionID=0)
  {
    try {
      $selectColumns = ['SubscriptionID',	'UserID',	'CourseID',	'SubscriptionReference',	'PaymentMethod',	'Reference',	'StartDate', 'EndDate', 'Status', 'CreatedAt',	'CreatedBy',	'UpdatedAt',	'UpdatedBy'	];
  
      $courseSubscription = $this->select($selectColumns)->where('SubscriptionID', $subscriptionID)->first();

      if ( !isset($courseSubscription) ) {
        return null;
      }      

      return $courseSubscription;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function getLatestCoursesSubscription($userId=0, $courseId=0)
  {
    try {
      $selectColumns = ['SubscriptionID',	'UserID',	'CourseID',	'SubscriptionReference',	'PaymentMethod',	'Reference',	'StartDate', 'EndDate', 'Status',	'CreatedAt',	'CreatedBy',	'UpdatedAt',	'UpdatedBy'	];
  
      $courseSubscription = $this->select($selectColumns)->where('UserID', $userId)->where('CourseID', $courseId)->orderBy('CreatedAt', 'DESC')->first();

      if ( !isset($courseSubscription) ) {
        return null;
      }      

      return $courseSubscription;

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function saveCourseSubscription($data=[])
  {
    return is_null($data) ? false : ( $this->insert($data) ? true : false );
  }

  public function updateCourseSubscription($courseSubscription=[], $status='')
  {
    
    if ( is_null($courseSubscription) ) { return false; }

    if ( isset($status) ) { $this->set('Status', $status); }

    return $this->where('SubscriptionID', $courseSubscription['SubscriptionID'])->update();
  }

}