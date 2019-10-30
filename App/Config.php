<?php
/**
  * @author: Gaël Robin
  *
**/
namespace App;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config
{

    /**
     * Database host
     * @var string
     */
    const DB_HOST = '';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = '';

    /**
     * Database user
     * @var string
     */
    const DB_USER = '';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
     */
    const SHOW_ERRORS = true;

    const BASE_URL = '/pimdna/';

    const HANGOUT_MSG = true;

    const SEND_MAIL = true;

    const SUSPICIOUS_ARR = ['base64_decode','base64_encode','md5','eval(','gzinflate', 'str_rot_13'];

    const DEV_MODE = true;
}
