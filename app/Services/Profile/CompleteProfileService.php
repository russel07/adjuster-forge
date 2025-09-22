<?php

namespace SmartySoft\AdjusterForge\Services\Profile;

defined('ABSPATH') || exit; // Prevent direct access

interface CompleteProfileService
{
    public function save(array $data, $user_id): bool;
    public function update(array $data, $user_id): bool;
}