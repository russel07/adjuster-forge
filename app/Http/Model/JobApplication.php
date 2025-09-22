<?php

namespace SmartySoft\AdjusterForge\Http\Model;

defined('ABSPATH') || exit; // Prevent direct access

class JobApplication extends Model
{
    protected $tableName = 'af_job_applications';

    /**
     * Retrieves a job application by the job ID for the current user.
     *
     * @param int $job_id The ID of the job application.
     * @return object|null The job application object if found, null otherwise.
     */
    public static function getByJobId( $job_id )
    {
        $model = static::getInstance();
        $applicant_id = get_current_user_id();
        $query = $model->_wpdb->prepare(
            "SELECT * FROM $model->tableName WHERE applicant_id = %d AND job_id = %d",
            $applicant_id,
            $job_id
        );
        
        return $model->_wpdb->get_row($query);
    }

    public static function getAllAppliedJobId() {
        $model = static::getInstance();
        $applicant_id = get_current_user_id();
        if ( ! $applicant_id ) {
            return [];
        }
        $job = self::getAllAppliedJobs($applicant_id);
        $job_ids = [];
        if ($job) {
            foreach ($job as $application) {
                $job_ids[] = $application->job_id;
            }
        }
        return $job_ids;
    }

    public static function getAllAppliedJobs( $applicant_id )
    {
        $model = static::getInstance();
        $query = $model->_wpdb->prepare(
            "SELECT * FROM $model->tableName WHERE applicant_id = %d ORDER BY application_date DESC",
            $applicant_id
        );

        return $model->_wpdb->get_results($query);
    }

    public static function getApplicantsByJobId( $job_id, $limit, $offset )
    {
        $model = static::getInstance();
        $query = $model->_wpdb->prepare(
            "SELECT * FROM $model->tableName WHERE job_id = %d ORDER BY application_date DESC LIMIT %d OFFSET %d",
            $job_id,
            $limit,
            $offset
        );
        
        return $model->_wpdb->get_results($query);
    }

    public function delete($id, $applicant_id){
        $query = $this->_wpdb->prepare(
            "DELETE FROM $this->tableName WHERE id = %d AND applicant_id = %d",
            $id,
            $applicant_id
        );
        return $this->_wpdb->query($query);
    }
}