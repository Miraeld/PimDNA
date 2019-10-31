<?php
/**
  * @author: Gaël Robin - Pimclick
  * @version: 1.1.0
  *
**/
namespace App\Controllers;

use \Core\View;
use \App\Config;
use \App\Controllers\Misc;
use \App\Controllers\Technical;
use \App\Controllers\Utilities;
use \App\Controllers\Login;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends \Core\Controller
{

    public function __construct()
    {
        $connexion = new Login();

        $connexion->checkConnexion();

    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {

      $utilities = new Utilities();

      $total_files = $utilities->countAllServerFiles('../..');



      /**
       * Get all technical infos of the server
       * @var Technical
       */
      $technical_info = new Technical();

      $disk_space = $technical_info->server_space();

      $args = array(
        'hostname'        => php_uname('n'),
        'os'              => $technical_info->os,
        'kernel'          => $technical_info->kernel,
        'uptime'          => $technical_info->uptime,
        'last_boot'       => $technical_info->last_boot,
        'cpu_model'       => $technical_info->cpu_model,
        'cpu_frequency'   => $technical_info->cpu_frequency,
        'cpu_cache'       => $technical_info->cpu_cache,
        'cpu_bogomips'    => $technical_info->cpu_bogomips,
        'cpu_temp'        => $technical_info->cpu_temp,
        'num_cores'       => $technical_info->num_cores,
        'server_date'     => $technical_info->server_date,
        'server_name'     => $_SERVER['HTTP_HOST'],
        'script_filename' => $_SERVER['SCRIPT_FILENAME'],
        'script_name'     => $_SERVER['SCRIPT_NAME'],
        'total_space'     => $utilities->formatBytes($disk_space['total_space']),
        'free_space'      => $utilities->formatBytes($disk_space['free_space']),
        'used_space'      => $utilities->formatBytes($disk_space['used_space']),
        'space_percent'   => number_format($disk_space['percent_space'], 2),
        'total_files'     => $total_files,
        'memory_used'     => $technical_info->memory['used'],
        'memory_free'     => $technical_info->memory['free'],
        'memory_total'    => $technical_info->memory['total'],
        'memory_p_used'   => $technical_info->memory['percent_used'],
        'services'        => $technical_info->services,
      );
      
      View::renderTemplate('Home/index.php', $args);
    }




}
