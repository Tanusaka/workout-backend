<?php
/**
 *
 * @author Samu
 */
namespace App\Models\App\Courses;

use CodeIgniter\Model;

class PaymentModel extends Model
{

	protected $table      = 'course_payments';
	protected $primaryKey = 'PaymentID';

	protected $protectFields    = false;

	// Dates
	protected $useTimestamps        = true;
	protected $dateFormat           = 'datetime';
	protected $createdField         = 'createdat';
	protected $updatedField         = 'createdby';

	// Callbacks
	protected $allowCallbacks       = true;
	// protected $beforeInsert         = [];
	// protected $afterInsert          = [];
	// protected $beforeUpdate         = [];
	// protected $afterUpdate          = [];
	// protected $beforeFind           = [];
	// protected $afterFind            = [];
	// protected $beforeDelete         = [];
	// protected $afterDelete          = [];


	public function getLastPayment($userid=0, $courseid=0)
	{
		try {

			$payment = $this->select()
			->where('userid', $userid)
			->where('courseid', $courseid)->orderBy('createdat', 'DESC')->first();

			return $payment;

		} catch (\Exception $e) {
		throw new \Exception($e->getMessage());
		}
	}

	public function savePayment($data=[])
	{
		return is_null($data) ? false : ( $this->insert($data) ? true : false );
	}

}