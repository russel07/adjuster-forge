<?php
namespace SmartySoft\AdjusterForge\Services\Profile;

use SmartySoft\AdjusterForge\Services\Profile\DriverProfileService;
use SmartySoft\AdjusterForge\Services\Profile\CompanyProfileService;
use SmartySoft\AdjusterForge\Services\Profile\CompleteProfileService;

class ProfileServiceFactory {
    /**
     * Returns the correct profile service based on user type
     * @param string $user_type
     * @return CompleteProfileService
     */
    public static function getProfileService($user_type): CompleteProfileService {
        if ($user_type === 'driver') {
            return new DriverProfileService();
        } else {
            return new CompanyProfileService();
        }
    }
}
