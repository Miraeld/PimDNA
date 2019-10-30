<?php
/**
  * @author: Gaël Robin
  *
**/
namespace App\Controllers;

use \Core\View;
use \App\Config;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Md5 extends \Core\Controller
{
    public $listfile = '';
    public $content_folder = array();
    /**
     * Show the index page
     *
     * @return void
     */

    public function __construct()
    {
        $this->listfile = '';
        $this->content_folder = array();
        return $this;
    }
    public function indexAction()
    {
      $this->checkConnexion();
      $dir = $_SERVER['DOCUMENT_ROOT'].'/';
      $file_exist = $this->exist_dna('../dna/');

      $list_pdna = scandir('../dna/');
      unset($list_pdna[array_search('.', $list_pdna, true)]);
      unset($list_pdna[array_search('..', $list_pdna, true)]);
      $path_pdna = $this->get_glob('../dna/');
      $f_list_pdna = array();
      if ($file_exist)
      {
        foreach ($path_pdna as $path)
        {
            $filename = str_replace('../dna/', '', $path);
            $tmp_ar = array('filename' => $filename, 'path' => $path);

            array_push($f_list_pdna, $tmp_ar);
        }
      }

      $args = array(
        'file_exist'    => $file_exist,
        'document_root' => $dir,
        'list_pdna'     => $f_list_pdna,

      );
      View::renderTemplate('Md5/index.php', $args);

    }

    public function generate()
    {
      ignore_user_abort(true);
      ini_set("memory_limit", "-1");
      set_time_limit(0);

      // $dir = $_SERVER['DOCUMENT_ROOT'];
      $dir = '../..';
      $this->listFolder($dir);

      if ($this->content_folder) {
        $total_entries = count($this->content_folder);
        $dna_file = "../dna/".date('d-m-Y-h-i-s').".pdna";
        $filesave = fopen($dna_file, 'w');

        foreach ($this->content_folder as $index => $info)
        {
          if (filesize($info['path']) < 2147483648)
          {
            $string =  str_replace('../..','', $info['path'])." || ".md5_file($info['path'])."\n";
            fwrite($filesave, $string);
          }


          $this->content_folder[$index] = null;
          unset($this->content_folder[$index]);
          //
          // ob_flush();
          // flush();
          sleep(0.000166667);
        }
        fclose($filesave);
      }
      // var_export($this->content_folder);
      ///

      $dir = $_SERVER['DOCUMENT_ROOT'];
      $file_exist = $this->exist_dna('../dna/');

      $list_pdna = scandir('../dna/');
      unset($list_pdna[array_search('.', $list_pdna, true)]);
      unset($list_pdna[array_search('..', $list_pdna, true)]);
      $path_pdna = $this->get_glob('../dna/');
      $f_list_pdna = array();
      if ($file_exist)
      {
        foreach ($path_pdna as $path)
        {
            $filename = str_replace('../dna/', '', $path);
            $tmp_ar = array('filename' => $filename, 'path' => $path);

            array_push($f_list_pdna, $tmp_ar);
        }
      }

      $args = array(
        'file_exist'      => $file_exist,
        'document_root'   => $dir,
        'list_pdna'       => $f_list_pdna,
        'generation_done' => true
      );
      if (Config::HANGOUT_MSG)
      {
        $hangout_msg = "PimDNA Report\n\n";
        $hangout_msg .= "Generation made for ".$_SERVER['SERVER_NAME']." the *".date('d-m-Y')."* at *".date('H:i:s')."*.";
        $this->send_hg_msg(0, $hangout_msg);
      }


      View::renderTemplate('Md5/index.php', $args);

    }

    private function send_hg_msg($type = 0, $hangout_msg)
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

    public function results()
    {
      $this->checkConnexion();
      $dir = $_SERVER['DOCUMENT_ROOT'].'/';
      $file_exist = $this->exist_dna($dir);
      $content_dir = scandir($dir, 1);
      unset($content_dir[array_search('.', $content_dir, true)]);
      unset($content_dir[array_search('..', $content_dir, true)]);
      $content_dir = '';
      $test = $this->listFolder($dir);

      // var_export($this->content);
      // exit();
      // $test = $this->listFolderFiles($dir);

      // str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['SCRIPT_SERVER'])
      $args = array(
        'file_exist'  => $file_exist,
        'content_dir' => $content_dir,
        'test'        => $_SERVER['DOCUMENT_ROOT']
      );
      View::renderTemplate('Md5/result.php', $args);
    }

    private function rutime($ru, $rus, $index)
    {
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
         -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }

    public function compare()
    {

      ini_set("memory_limit", "-1");
      set_time_limit(0);

      $file_no_change = array();
      $file_changed = array();
      $file_removed = array();
      $rustart = getrusage();

      $dir = '../..';
      $this->listFolder($dir);

      $files = scandir('../dna/', SCANDIR_SORT_DESCENDING);
      $dna_reference = $files[0];
      $filename = '../dna/'.$dna_reference;
      $dna_file = fopen($filename, 'r');
      if ($dna_file)
      {
        $array_fileline = explode("\n", fread($dna_file, filesize($filename)));
      }
      $ref_dna = array();
      foreach ($array_fileline as $index => $value) {
        $val = explode(' || ', $value);
        if (!empty($val[0])) {
          $existing_index = ($this->search_in_dna($val[0]));

          if ($existing_index) {
            if (md5_file($this->content_folder[$existing_index]['path']) == $val[1]) {
              // echo "MD5 SIMILAR<br>";
              array_push($file_no_change, array('path' => $val[0]));
            } else {
              // echo "MD5 DIFFERENT<br>";
              array_push($file_changed, array('path' => $val[0]));
            }
            $this->content_folder[$existing_index] = null;
            unset($this->content_folder[$existing_index]);
          } else {
            // echo "FILE REMOVED<br>";
            array_push($file_removed, array('path' => $val[0]));
            $this->content_folder[$existing_index] = null;
            unset($this->content_folder[$existing_index]);
          }

        }
        // ob_flush();
        flush();
        sleep(0.000166667);
      }

      $ru = getrusage();
      $time_processed =  $this->rutime($ru, $rustart, "utime").' ms';
      $test = $file_changed;
      $file_added = $this->content_folder;
      $directory = dirname(__FILE__);
      $checkdisk = array(
        'total_space' => disk_total_space($directory),
        'free_space'  => disk_free_space($directory),
        'used_space'  => disk_total_space($directory)-disk_free_space($directory),
        'percent'     => ((disk_total_space($directory)-disk_free_space($directory))/disk_total_space($directory))*100,
      );
      ob_flush();
      flush();
      if (Config::SEND_MAIL)
      {
        $mail_result = $this->send_email($file_changed, $file_removed, $file_added, count($file_no_change), false, $checkdisk);
      } else {
        $mail_result = false;
      }
      $searchforArr = Config::SUSPICIOUS_ARR;
      $suspicious_file = array();
      foreach ($file_added as $added)
      {
        $contents = file_get_contents($added['path']);
        foreach ($searchforArr as $searchfor)
        {
          $pattern = preg_quote($searchfor, '/');
          $detected = $pattern;
          // finalise the regular expression, matching the whole line
          $pattern = "/^.*$pattern.*\$/m";
          if(preg_match_all($pattern, $contents, $matches))
          {
            $added['suspicious'] = implode("\n", $matches[0]);
            array_push($suspicious_file, $added);
            // echo "Files: ".$added['filename']." - Found matches:\n";
            // echo implode("\n", $matches[0]);
          }
        }
      }

      foreach ($file_changed as $modified)
      {
        $contents = file_get_contents('../..'.$modified['path']);
        foreach ($searchforArr as $searchfor)
        {
          $pattern = preg_quote($searchfor, '/');
          $detected = $pattern;
          // finalise the regular expression, matching the whole line
          $pattern = "/^.*$pattern.*\$/m";
          if(preg_match_all($pattern, $contents, $matches))
          {
            $modified['suspicious'] = implode("\n", $matches[0]);
            array_push($suspicious_file, $modified);

          }
        }
      }

      $args = array(
        'file_changed_count'    => count($file_changed),
        'file_modified'         => $file_changed,
        'file_removed_count'    => count($file_removed),
        'file_removed'          => $file_removed,
        'file_no_change_count'  => count($file_no_change),
        'time_processed'        => $time_processed,
        'file_added_count'      => count($file_added),
        'file_added'            => $file_added,
        'test'                  => $test,
        'mail_result'           => $mail_result,
        'files_suspicious'      => $suspicious_file,
      );
      if (Config::HANGOUT_MSG)
      {
        $total_files = count($file_changed) + count($file_removed) + count($file_no_change) + count($file_added);
        $hangout_msg = "PimDNA Comparison for ".$_SERVER['SERVER_NAME']."\n\n";
        $hangout_msg .= "Comparison made the ". date('d-m-Y')." at ".date('H:i:s')."\n";
        $hangout_msg .= "```\n";
        $hangout_msg .= " - ". count($file_changed)." Changed Files,\n";
        $hangout_msg .= " - ". count($file_removed)." Removed Files,\n";
        $hangout_msg .= " - ". count($file_added)." Added Files,\n";
        $hangout_msg .= " - ". count($suspicious_file)." Suspicious Detections within modified/added files.\n";
        $hangout_msg .= "``` \n";
        $hangout_msg .= "Total of *".$total_files."* files processed.\n";
        $hangout_msg .= "Report made in *$time_processed*. \n\n";

        $hangout_msg .= "Please refer to the email report for more information.";

        $this->send_hg_msg(1, $hangout_msg);
      }

      View::renderTemplate('Md5/result.php', $args);

    }

    public function compare_cron()
    {
      if ($_POST['login'] == '179832606881eb45817a6ca11be3aacd' && $_POST['password'] == 'd8be8632e84115f22df5cb55f2c24a2b')
      // if (true)
      {
          // $_POST['contact'] = false;
          // var_export();
          ini_set("memory_limit", "-1");
          set_time_limit(0);

          $file_no_change = array();
          $file_changed = array();
          $file_removed = array();
          $rustart = getrusage();


          // $dir = $_SERVER['DOCUMENT_ROOT'];
          $dir = '../..';
          $this->listFolder($dir);

          $files = scandir('../dna/', SCANDIR_SORT_DESCENDING);
          $dna_reference = $files[0];
          $filename = '../dna/'.$dna_reference;
          $dna_file = fopen($filename, 'r');
          if ($dna_file)
          {
            $array_fileline = explode("\n", fread($dna_file, filesize($filename)));
          }
          $ref_dna = array();
          foreach ($array_fileline as $index => $value) {
            $val = explode(' || ', $value);
            if (!empty($val[0])) {
              $existing_index = ($this->search_in_dna($val[0]));

              if ($existing_index) {
                if (md5_file($this->content_folder[$existing_index]['path']) == $val[1]) {
                  // echo "MD5 SIMILAR<br>";
                  array_push($file_no_change, array('path' => $val[0]));
                } else {
                  // echo "MD5 DIFFERENT<br>";
                  array_push($file_changed, array('path' => $val[0]));
                }
                $this->content_folder[$existing_index] = null;
                unset($this->content_folder[$existing_index]);
              } else {
                // echo "FILE REMOVED<br>";
                array_push($file_removed, array('path' => $val[0]));
                $this->content_folder[$existing_index] = null;
                unset($this->content_folder[$existing_index]);
              }

            }
            // ob_flush();
            flush();
            sleep(0.0005);
          }

          $ru = getrusage();
          $time_processed =  $this->rutime($ru, $rustart, "utime").' ms';
          $test = $file_changed;
          $file_added = $this->content_folder;
          $directory = dirname(__FILE__);
          $checkdisk = array(
            'total_space' => disk_total_space($directory),
            'free_space'  => disk_free_space($directory),
            'used_space'  => disk_total_space($directory)-disk_free_space($directory),
            'percent'     => ((disk_total_space($directory)-disk_free_space($directory))/disk_total_space($directory))*100,
          );
          flush();
          $searchforArr = Config::SUSPICIOUS_ARR;
          $suspicious_file = array();
          foreach ($file_added as $added)
          {
            $contents = file_get_contents($added['path']);
            foreach ($searchforArr as $searchfor)
            {
              $pattern = preg_quote($searchfor, '/');
              $detected = $pattern;
              // finalise the regular expression, matching the whole line
              $pattern = "/^.*$pattern.*\$/m";
              if(preg_match_all($pattern, $contents, $matches))
              {
                $added['suspicious'] = implode("\n", $matches[0]);
                array_push($suspicious_file, $added);
                // echo "Files: ".$added['filename']." - Found matches:\n";
                // echo implode("\n", $matches[0]);
              }
            }
          }

          foreach ($file_changed as $modified)
          {
            $contents = file_get_contents('../..'.$modified['path']);
            foreach ($searchforArr as $searchfor)
            {
              $pattern = preg_quote($searchfor, '/');
              $detected = $pattern;
              // finalise the regular expression, matching the whole line
              $pattern = "/^.*$pattern.*\$/m";
              if(preg_match_all($pattern, $contents, $matches))
              {
                $modified['suspicious'] = implode("\n", $matches[0]);
                array_push($suspicious_file, $modified);

              }
            }
          }

          ob_flush();
          flush();

          if (Config::SEND_MAIL)
          {
            $mail_result = $this->send_email($file_changed, $file_removed, $file_added, count($file_no_change), $_POST['contact'], $checkdisk, $suspicious_file);
          }
          else
          {
            $mail_result = true;
          }


          if (Config::HANGOUT_MSG)
          {
            $total_files = count($file_changed) + count($file_removed) + count($file_no_change) + count($file_added);
            $hangout_msg = "PimDNA Comparison for ".$_SERVER['SERVER_NAME']."\n\n";
            $hangout_msg .= "Comparison made the ". date('d-m-Y')." at ".date('H:i:s')."\n";
            $hangout_msg .= "```\n";
            $hangout_msg .= " - ". count($file_changed)." Changed Files,\n";
            $hangout_msg .= " - ". count($file_removed)." Removed Files,\n";
            $hangout_msg .= " - ". count($file_added)." Added Files,\n";
            $hangout_msg .= " - ". count($suspicious_file)." Suspicious Detections within modified/added files.\n";
            $hangout_msg .= "``` \n";
            $hangout_msg .= "Total of *".$total_files."* files processed.\n";
            $hangout_msg .= "Report made in *$time_processed*. \n\n";

            $hangout_msg .= "Please refer to the email report for more information.";

            $this->send_hg_msg(1, $hangout_msg);
          }


          return true;


      } else {
        return json_encode(array('status' => false, 'msg' => 'Wrong Username and password.'));
      }

    }

    public function generate_cron()
    {
      if ($_POST['login'] == '179832606881eb45817a6ca11be3aacd' && $_POST['password'] == 'd8be8632e84115f22df5cb55f2c24a2b')
      {

        ignore_user_abort(true);
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        $dir = '../..';
        $this->listFolder($dir);

        if ($this->content_folder) {
          $total_entries = count($this->content_folder);
          $filename_dna ="../dna/".date('d-m-Y-h-i-s').".pdna";
          $filesave = fopen($filename_dna, 'w');

          foreach ($this->content_folder as $index => $info)
          {

            if (filesize($info['path']) < 2147483648)
            {
              $string =  str_replace('../..','', $info['path'])." || ".md5_file($info['path'])."\n";
              fwrite($filesave, $string);
            }

            $this->content_folder[$index] = null;
            unset($this->content_folder[$index]);
            //
            // ob_flush();
            // flush();
            sleep(0.000166667);
          }
          fclose($filesave);
        }
        // var_export();
        if (Config::SEND_MAIL)
        {
            $this->send_generate_email($_POST['contact'], $filename_dna);
        }

        if (Config::HANGOUT_MSG)
        {
          $hangout_msg = "PimDNA Report\n\n";
          $hangout_msg .= "Generation made for ".$_SERVER['SERVER_NAME']." the *".date('d-m-Y')."* at *".date('H:i:s')."*.";
          $this->send_hg_msg(0, $hangout_msg);
        }

        return true;


      } else {
        return json_encode(array('status' => false, 'msg' => 'Wrong Username and password.'));
      }

    }

    private function send_generate_email($contact = false, $filename)
    {
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $domainName = $_SERVER['HTTP_HOST'];

      $message = '<html><body>';
      $message .= '<div style="border: 1px solid gray; border-radius:15px;">';
      $message .= '<div style="display:block; background-color:#dcdcdc; padding:10px 30px;     border-top-right-radius: 15px;     border-top-left-radius: 15px;">';
      $message .= '<img src="https://www.pimclick.com/wp-content/uploads/pimnutella.png"  alt="Pimclick" style="display:inline-block; float:right; margin-top:-10px;"/>';

      $message .= '<h1 style="display:inline-block;">PimDNA</h1>';
      $message .= '</div>';
      $message .= '<div style="padding:60px 30px 0px 30px;">';
      $message .= '<p style="font-weight:bold;">PDNA File generated with success</p>';
      // $message .= '<p style="font-weight:bold;">Please, pay attention to any suspicious file. Do not hesitate to analyze them.';
      $message .= '</div>';
      $message .= '<div style="padding:30px;">';

      $message .= '<p>PDNA File generated - '. Date('d-m-Y H:i').' </p>';

      $message .= '</div>';
      $message .= '</div>';
      $message .= '</html></body>';

      $message = wordwrap($message, 70, "\r\n");

      $mail = new PHPMailer(true);
      try {

          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'in-v3.mailjet.com';                    // Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = '09639ebd3cb8610b962b7fe000963dbf';                     // SMTP username
          $mail->Password   = 'c8e98069c9872a97b406d5875bc2737b';                               // SMTP password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
          $mail->Port       = 587;                                    // TCP port to connect to

          //Recipients
          $mail->setFrom('gael@luxury-concept.com', 'PimDNA');
          if ($contact)
          {
            $contact = json_decode($contact, true);

            foreach ($contact as $c):
              $mail->addAddress($c[0]);
            endforeach;
          }

          else
            $mail->addAddress('gael@pimclick.com');

          // Attachments
          $mail->addAttachment($filename);         // Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = '[PimDNA] '.$domainName.' - Generation '. date('d-m-Y H:i');
          $mail->Body    = $message;
          // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

          $mail->send();
        } catch (Exception $e) {
          echo $e;
        }


    }



    private function send_email($file_changed, $file_removed, $file_added, $file_no_change, $contact = false, $checkdisk = false, $suspicious_file = false)
    {
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
      $domainName = $_SERVER['HTTP_HOST'];

      $message = '<html><body>';
      $message .= '<div style="border: 1px solid gray; border-radius:15px;">';
      $message .= '<div style="display:block; background-color:#dcdcdc; padding:10px 30px;     border-top-right-radius: 15px;     border-top-left-radius: 15px;">';
      $message .= '<img src="https://www.pimclick.com/wp-content/uploads/pimnutella.png"  alt="Pimclick" style="display:inline-block; float:right; margin-top:-10px;"/>';

      $message .= '<h1 style="display:inline-block;">Report PimDNA</h1>';
      $message .= '</div>';
      $message .= '<div style="padding:60px 30px 0px 30px;">';
      $message .= '<p style="font-weight:bold;">Please, pay attention to any suspicious file. Do not hesitate to analyze them.';
      $message .= '</div>';
      $message .= '<div style="padding:30px;">';

      $message .= '<h2>Summary</h2>';
      $message .= '<p><span style="font-weight:bold;">Website analyzed: </span>'. $protocol.$domainName .'</p>';
      $message .= '<p><span style="font-weight:bold; color:green;">Files Not Changed:</span> '.$file_no_change.' files</p>';
      $message .= '<p><span style="font-weight:bold; color:orange;">Files Modified:</span> '.count($file_changed).' files</p>';
      $message .= '<p><span style="font-weight:bold; color:red;">Files Deleted:</span> '.count($file_removed).' files</p>';
      $message .= '<p><span style="font-weight:bold; color:orange;">Files Added:</span> '.count($file_added).' files</p>';
      if ($suspicious_file) {
          $message .= '<p><span style="font-weight:bold; color:red;">Suspicious Detections:</span> '.count($suspicious_file).' detections</p>';
      }


      if ($checkdisk)
      {
        $message .= '<hr>';
        $message .= '<p><span style="font-weight:bold;">Total Disk Space:</span> '.$this->formatBytes($checkdisk['total_space']).' </p>';
        $message .= '<p><span style="font-weight:bold;">Disk Free:</span> '.$this->formatBytes($checkdisk['free_space']).' </p>';
        $message .= '<p><span style="font-weight:bold;">Disk Overview:</span> '.(number_format($checkdisk['percent'], 2)).'% used </p>';
      }

      $message .= '<hr>';

      $message .= '<h2>List of Modified Files</h2>';
      foreach ($file_changed as $changed)
      {
          $message .= '<p style="padding-left:30px">'.$changed['path'].'</p>';
          $changed = null;
      }

      $message .= '<h2>List of Deleted Files</h2>';
      foreach ($file_removed as $removed)
      {
          $message .= '<p style="padding-left:30px">'.$removed['path'].'</p>';
          $removed = null;
      }

      $message .= '<h2>List of Added Files</h2>';

      foreach ($file_added as $added)
      {

          $message .= '<p style="padding-left:30px">'.str_replace('../..','', $added['path']).'</p>';
          $added = null;
      }
      $message .= '<h2 style="color:red;">List of Suspicious Detections</h2>';
      if ($suspicious_file)
        foreach ($suspicious_file as $suspicious)
        {
            $message .= '<p style="padding-left:30px">'.$suspicious['path'].'</p>';
            $message .= '<p style="padding-left:60px">'.$suspicious['suspicious'].'</p>';
            $suspicious = null;
        }
      $message .= '</div>';


      $message .= '</div>';

      $message .= '</html></body>';


      $message = wordwrap($message, 70, "\r\n");

      $mail = new PHPMailer(true);
      try {

          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'in-v3.mailjet.com';                    // Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = '09639ebd3cb8610b962b7fe000963dbf';                     // SMTP username
          $mail->Password   = 'c8e98069c9872a97b406d5875bc2737b';                               // SMTP password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
          $mail->Port       = 587;                                    // TCP port to connect to

          //Recipients
          $mail->setFrom('gael@luxury-concept.com', 'PimDNA');
          if ($contact) {
            $contact = json_decode($contact, true);

            foreach ($contact as $c):
              $mail->addAddress($c[0]);
            endforeach;
          }

          else
            $mail->addAddress('gael@pimclick.com');

          // Attachments
          // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = '[PimDNA] '.$domainName.' - Report '. date('d-m-Y h:i:s');
          $mail->Body    = $message;
          // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

          $mail->send();
          // echo 'Message has been sent';
          return true;
      } catch (Exception $e) {
          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
          return false;
      }



    }

    private function search_in_dna($path)
    {
      foreach ($this->content_folder as $index => $value)
      {
        if (str_replace('../..', '', $value['path']) == $path)
        {
          return $index;
        }
      }
      return null;
    }

    private function exist_dna($dir)
    {
      if (glob($dir.'*.pdna'))
      {
        return true;
      } else {
        return false;
      }
    }

    private function get_glob($dir)
    {
      if (glob($dir.'*.pdna'))
      {
        return glob($dir.'*.pdna');
      } else {
        return false;
      }
    }

    private function listFolderFiles($dir)
    {

      $ffs = scandir($dir);

      unset($ffs[array_search('.', $ffs, true)]);
      unset($ffs[array_search('..', $ffs, true)]);

      // prevent empty ordered elements
      if (count($ffs) < 1)
          return;

      $this->listfile .= '<ol>';
      foreach($ffs as $ff){
          $this->listfile .= '<li>'.$ff;
          if(is_dir($dir.'/'.$ff)) $this->listFolderFiles($dir.'/'.$ff);
          $this->listfile .= '</li>';
      }
      $this->listfile .= '</ol>';
    }

    private function listFolder($dir)
    {
      $tmp_content = scandir($dir);
      $tmp_list_file = array();
      unset($tmp_content[array_search('.', $tmp_content, true)]);
      unset($tmp_content[array_search('..', $tmp_content, true)]);
      foreach ($tmp_content as $file)
      {

        if (is_dir($dir.'/'.$file)) {
            $this->listFolder($dir.'/'.$file);
        }
        else
        {
          array_push($this->content_folder, array('filename' => $file, 'path' => $dir.'/'.$file));
        }
      }
      // array_push($this->content_folder, $tmp_list_file);
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

    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
