<?php
/**
 * Admin Authentication Guard
 */

require_once __DIR__ . '/../../app/helpers/helpers.php';

require_login();
$currentUser = current_user();
