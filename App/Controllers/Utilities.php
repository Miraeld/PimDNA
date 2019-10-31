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
   * Utilities controller
   */
  class Utilities extends \Core\Controller
  {
    private $total_files = 0;

    public function __construct()
    {
      return $this;
    }

    /**
     * Bytes to readable value
     * @param  int  $bytes        Bytes value to convert
     * @param  integer $precision Precision of the conversion
     * @return string             Readable value with Unit
     */
    public function formatBytes($bytes, $precision = 2)
    {
  			$units = array('B', 'KB', 'MB', 'GB', 'TB');

  			$bytes = max($bytes, 0);
  			$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
  			$pow = min($pow, count($units) - 1);

  			$bytes /= pow(1024, $pow);

  			return round($bytes, $precision) . ' ' . $units[$pow];
		}

    /**
     * Count files within a dir
     * @param  string $dir Dir where we have to count, by default ../..
     * @return int         Total count of files
     */
    public function countAllServerFiles($dir = '../..')
    {

      $tmp_content = scandir($dir);
      $tmp_list_file = array();
      unset($tmp_content[array_search('.', $tmp_content, true)]);
      unset($tmp_content[array_search('..', $tmp_content, true)]);
      foreach ($tmp_content as $file)
      {

        if (is_dir($dir.'/'.$file)) {
            $this->countAllServerFiles($dir.'/'.$file);
        }
        else
        {
          $this->total_files++;
        }
      }
      return $this->total_files;
    }

    /**
     * Get the authentification token for the API
     * @return string Encrypted Token
     */
    public function getToken()
    {
        return md5("PimDNA".$_SERVER['SERVER_NAME']);
    }

    /**
     * Send message on Hangout
     * @param  integer $type        0 = Generation / 1 = Compare
     * @param  string  $hangout_msg Message sent
     * @return true
     */
    public function send_hg_msg($type = 0, $hangout_msg)
    {
      if ($type == 0)
        $url = 'https://chat.googleapis.com/v1/spaces/AAAAAtDsBjY/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=xK-3w-XOWRKF3COWe92Ti2UAsE4lR010YT48HOGg-Uo%3D';
      else
        $url = 'https://chat.googleapis.com/v1/spaces/AAAAAtDsBjY/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=vDOYoZiE-X8kUWkIsQepFhuLExMsp5iPadjZRDvWKys%3D';

      if (Config::DEV_MODE) {
        $url = 'https://chat.googleapis.com/v1/spaces/AAAAEr0XuOI/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=GLThTxD27qr84UZeVtlFvW-YeiVcb1LUejkghl_5YYk%3D';
      }

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('text' => $hangout_msg)));
      curl_setopt($ch, CURLOPT_POST, 1);
      $result = curl_exec ($ch);
      curl_close ($ch);

      return true;
    }

    /**
     * Get the processed time
     * @param  [type] $ru    [description]
     * @param  [type] $rus   [description]
     * @param  [type] $index [description]
     * @return [type]        [description]
     */
    public function rutime($ru, $rus, $index)
    {
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
         -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }

  }
