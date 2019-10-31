<?php
/**
 * @author: Gael Robin - Pimclick
 * @version: 1.1.0
 *
 */
  namespace App\Controllers;

  use \App\Config;
  use \App\Controllers\Misc;

  /**
   * Server Technical Informations controller
   */
  class Technical extends \Core\Controller
  {

    public $os;
    public $kernel;
    public $uptime;
    public $last_boot;
    public $server_date;
    public $cpu_model;
    public $cpu_frequency;
    public $cpu_cache;
    public $cpu_bogomips;
    public $cpu_temp;
    public $memory = array();
    public $num_cores;
    public $services;

    /**
     * Contructor of Technical Class
     */
    public function __construct($load = true)
    {
      if ($load) {
        $this->os = $this->getOs();
        $this->kernel = $this->getKernel();
        $this->uptime = $this->getUptime();
        $this->last_boot = $this->getLastBoot();
        $this->server_date = $this->getServerDatetime();
        $this->getCPUInfo();
        $this->cpu_temp = $this->getCPUTemp();
        $this->memory = $this->getMemoryInfos();
        $this->num_cores = Misc::getCpuCoresNumber();
        $this->services = $this->get_services_infos();
      }


      return $this;
    }

    /**
     * Return the OS of the server
     * @return string [OS of the server if found else N.A]
     */
    private function getOs()
    {
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
      return $os;

    }

    /**
     * Return the Kernel of the server
     * @return string Kernel of the server
     */
    private function getKernel()
    {
      // Kernel
      if (!($kernel = shell_exec('/bin/uname -r')))
      {
          $kernel = 'N.A';
      }

      return $kernel;
    }

    /**
     * Get uptime of the server
     * @return string Uptime of the server
     */
    private function getUptime()
    {
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
      return $uptime;
    }

    /**
     * Get the last boot of the server
     * @return string Return the last boot the server
     */
    private function getLastBoot()
    {
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
      return $last_boot;
    }

    /**
     * Server Datetime
     * @return string Datetime of the server
     */
    private function getServerDatetime()
    {
      // Server datetime
      if (!($server_date = shell_exec('/bin/date')))
      {
          $server_date = date('Y-m-d H:i:s');
      }

      return $server_date;
    }

    /**
    * Get all CPU Informations
    * @return void
    */
    private function getCPUInfo()
    {
      $model      = 'N.A';
      $frequency  = 'N.A';
      $cache      = 'N.A';
      $bogomips   = 'N.A';

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
      $this->cpu_model = $model;
      $this->cpu_frequency = $frequency;
      $this->cpu_cache = $cache;
      $this->cpu_bogomips = $bogomips;
    }

    /**
     * Get temperature of the CPU
     * @return string Temperature of CPU
     */
    private function getCPUTemp()
    {
      $temp = "N.A";
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
      return $temp;
    }

    /**
     * Get Memory Informations
     * @return array Memory Informations
     */
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

    /**
     * Get Services Informations
     * @return array List of status of services
     */
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

    /**
     * Get all infos about disk space
     * @return array All infos about disk space
     */
    public function server_space()
    {
      $directory = dirname(__FILE__);
      $total_space = disk_total_space($directory);
      $free_space = disk_free_space($directory);
      $used_space = $total_space - $free_space;

      $percent = (($used_space/$total_space)*100);

      return array('total_space' => $total_space, 'free_space' => $free_space, 'used_space' => $used_space, 'percent_space' => $percent);
    }
  }
