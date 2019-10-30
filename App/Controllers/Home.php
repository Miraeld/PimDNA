<?php
/**
  * @author: Gaël Robin
  *
**/
namespace App\Controllers;

use \Core\View;
use \App\Config;
use \App\Controllers\Misc;


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

    public $total_files = 0;

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
      // var_export($_SESSION);
      $total_space = false;
      $free_space = false;
      $used_space = false;
      $percent = false;
      $this->checkConnexion();
      $num_cores = Misc::getCpuCoresNumber();
			$directory = dirname(__FILE__);
			$total_space 	= disk_total_space($directory);
			$free_space 	= disk_free_space($directory);
			$used_space 	= $total_space-$free_space;
			$percent 		= (($used_space/$total_space) * 100);
      $this->count_total_files('../..');

      if (!($os = shell_exec('/usr/bin/lsb_release -ds | cut -d= -f2 | tr -d \'"\'')))
      {
          if(!($os = shell_exec('cat /etc/system-release | cut -d= -f2 | tr -d \'"\'')))
          {
              if (!($os = shell_exec('find /etc/*-release -type f -exec cat {} \; | grep PRETTY_NAME | tail -n 1 | cut -d= -f2 | tr -d \'"\'')))
              {
                  $os = 'N.A';
              }
          }
      }
      $os = trim($os, '"');
      $os = str_replace("\n", '', $os);

      // Kernel
      if (!($kernel = shell_exec('/bin/uname -r')))
      {
          $kernel = 'N.A';
      }
      // Uptime
      if (!($totalSeconds = shell_exec('/usr/bin/cut -d. -f1 /proc/uptime')))
      {
          $uptime = 'N.A';
      }
      else
      {
          $uptime = '';
          $uptime = Misc::getHumanTime($totalSeconds);
      }

      // Last boot
      if (!($upt_tmp = shell_exec('cat /proc/uptime')))
      {
          $last_boot = 'N.A';
      }
      else
      {
          $upt = explode(' ', $upt_tmp);
          $last_boot = date('Y-m-d H:i:s', time() - intval($upt[0]));
      }
      // Server datetime
      if (!($server_date = shell_exec('/bin/date')))
      {
          $server_date = date('Y-m-d H:i:s');
      }
      ///// CPU


      $model      = 'N.A';
      $frequency  = 'N.A';
      $cache      = 'N.A';
      $bogomips   = 'N.A';
      $temp       = 'N.A';

      if ($cpuinfo = shell_exec('cat /proc/cpuinfo'))
      {
          $processors = preg_split('/\s?\n\s?\n/', trim($cpuinfo));

          foreach ($processors as $processor)
          {
              $details = preg_split('/\n/', $processor, -1, PREG_SPLIT_NO_EMPTY);

              foreach ($details as $detail)
              {
                  list($key, $value) = preg_split('/\s*:\s*/', trim($detail));

                  switch (strtolower($key))
                  {
                      case 'model name':
                      case 'cpu model':
                      case 'cpu':
                      case 'processor':
                          $model = $value;
                      break;

                      case 'cpu mhz':
                      case 'clock':
                          $frequency = $value.' MHz';
                      break;

                      case 'cache size':
                      case 'l2 cache':
                          $cache = $value;
                      break;

                      case 'bogomips':
                          $bogomips = $value;
                      break;
                  }
              }
          }
      }
      // CPU Temp
      if (exec('/usr/bin/sensors | grep -E "^(CPU Temp|Core 0)" | cut -d \'+\' -f2 | cut -d \'.\' -f1', $t))
      {
          if (isset($t[0]))
              $temp = $t[0].' °C';
      }
      else
      {
          if (exec('cat /sys/class/thermal/thermal_zone0/temp', $t))
          {
              $temp = round($t[0] / 1000).' °C';
          }
      }

      $memory = $this->getMemoryInfos();
      $services = $this->get_services_infos();

      // var_export($services);

      $args = array(
        'hostname'        => php_uname('n'),
        'os'              => $os,
        'kernel'          => $kernel,
        'uptime'          => $uptime,
        'last_boot'       => $last_boot,
        'cpu_model'       => $model,
        'cpu_frequency'   => $frequency,
        'cpu_cache'       => $cache,
        'cpu_bogomips'    => $bogomips,
        'cpu_temp'        => $temp,
        'num_cores'       => $num_cores,
        'server_date'     => $server_date,
        'server_name'     => $_SERVER['HTTP_HOST'],
        'script_filename' => $_SERVER['SCRIPT_FILENAME'],
        'script_name'     => $_SERVER['SCRIPT_NAME'],
        'total_space'     => $this->formatBytes($total_space),
        'free_space'      => $this->formatBytes($free_space),
        'used_space'      => $this->formatBytes($used_space),
        'space_percent'   => number_format($percent, 2),
        'total_files'     => $this->total_files,
        'memory_used'     => $memory['used'],
        'memory_free'     => $memory['free'],
        'memory_total'    => $memory['total'],
        'memory_p_used'   => $memory['percent_used'],
        'services'        => $services,
      );
      View::renderTemplate('Home/index.php', $args);
    }

    public function formatBytes($bytes, $precision = 2)
    {
  			$units = array('B', 'KB', 'MB', 'GB', 'TB');

  			$bytes = max($bytes, 0);
  			$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  			$pow = min($pow, count($units) - 1);

  			$bytes /= pow(1024, $pow);

  			return round($bytes, $precision) . ' ' . $units[$pow];
		}
    private function getMemoryInfos()
    {
      $free = 0;

      if (shell_exec('cat /proc/meminfo'))
      {
          $free    = shell_exec('grep MemFree /proc/meminfo | awk \'{print $2}\'');
          $buffers = shell_exec('grep Buffers /proc/meminfo | awk \'{print $2}\'');
          $cached  = shell_exec('grep Cached /proc/meminfo | awk \'{print $2}\'');

          $free = (int)$free + (int)$buffers + (int)$cached;
      }

      // Total
      if (!($total = shell_exec('grep MemTotal /proc/meminfo | awk \'{print $2}\'')))
      {
          $total = 0;
      }

      // Used
      $total = (int)$total;
      $used = $total - $free;

      // Percent used
      $percent_used = 0;
      if ($total > 0)
          $percent_used = 100 - (round($free / $total * 100));


      $datas = array(
          'used'          => Misc::getSize($used * 1024),
          'free'          => Misc::getSize($free * 1024),
          'total'         => Misc::getSize($total * 1024),
          'percent_used'  => $percent_used,
      );
      return $datas;
    }

    private function get_services_infos()
    {
      $datas = array();

      $available_protocols = array('tcp', 'udp');

      $show_port = true;

      $services_list = [[
                            "name"=> "Web Server",
                            "host"=> "localhost",
                            "port"=> 80,
                            "protocol"=> "tcp"
                        ],
                        [
                            "name"=> "Email Server (incoming)",
                            "host"=> "localhost",
                            "port"=> 993,
                            "protocol"=> "tcp"
                        ],
                        [
                            "name"=> "Email Server (outgoing)",
                            "host"=> "localhost",
                            "port"=> 587,
                            "protocol"=> "tcp"
                        ],
                        [
                            "name"=> "FTP Server",
                            "host"=> "localhost",
                            "port"=> 21,
                            "protocol"=> "tcp"
                        ],
                        [
                            "name"=> "Database Server",
                            "host"=> "localhost",
                            "port"=> 3306,
                            "protocol"=> "tcp"
                        ],
                        [
                            "name"=> "SSH",
                            "host"=> "localhost",
                            "port"=> 22,
                            "protocol"=> "tcp"
                        ]];

      if (true)
      {
          foreach ($services_list as $service)
          {
              $host     = $service['host'];
              $port     = $service['port'];
              $name     = $service['name'];
              $protocol = isset($service['protocol']) && in_array($service['protocol'], $available_protocols) ? $service['protocol'] : 'tcp';

              if (Misc::scanPort($host, $port, $protocol))
                  $status = 1;
              else
                  $status = 0;

              $datas[] = array(
                  'port'      => $show_port === true ? $port : '',
                  'name'      => $name,
                  'status'    => $status,
              );
          }
      }
      return $datas;

    }

    private function count_total_files($dir)
    {
      $tmp_content = scandir($dir);
      $tmp_list_file = array();
      unset($tmp_content[array_search('.', $tmp_content, true)]);
      unset($tmp_content[array_search('..', $tmp_content, true)]);
      foreach ($tmp_content as $file)
      {

        if (is_dir($dir.'/'.$file)) {
            $this->count_total_files($dir.'/'.$file);
        }
        else
        {
          $this->total_files++;
        }
      }
    }

    public function login()
    {

      if (!isset($_SESSION) || empty($_SESSION))
      {
          View::renderTemplate('Home/login.php');

      } else {
        header("Location: /pimdna/public/home");
      }

    }

    public function checklogin()
    {


      if ((md5($_POST['pimdna_login']) == '179832606881eb45817a6ca11be3aacd') && (md5($_POST['pimdna_password']) == 'd8be8632e84115f22df5cb55f2c24a2b'))
      {
        session_start();
        $_SESSION['start'] = time();
        $_SESSION['expire'] = $_SESSION['start'] + (60 * 60);
        header("Location: /pimdna/public/home");
      } else {
        $args = array(
          'error' => true,
          'msg'   => 'Wrong Username or Password',
        );
        View::renderTemplate('Home/login.php', $args);
        exit();
      }
    }

    public function checkConnexion()
    {
      session_start();
      if (!isset($_SESSION) || empty($_SESSION))
      {
          header('Location: /pimdna/public/home/login');
          exit();
      } else {
        if (time() > $_SESSION['expire'])
        {
            session_destroy();
            $_SESSION = [];
            header('Location: /pimdna/public/home/login');
        }
      }
    }
    public function test()
    {

      $url = 'https://chat.googleapis.com/v1/spaces/AAAAAtDsBjY/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=xK-3w-XOWRKF3COWe92Ti2UAsE4lR010YT48HOGg-Uo%3D';
      $r = new HttpRequest($url, HttpRequest::METH_POST);

      $r->addPostFields(array('user' => 'mike', 'pass' => 's3c|r3t'));
      
      try {
          echo $r->send()->getBody();
      } catch (HttpException $ex) {
          echo $ex;
      }
    }
}
