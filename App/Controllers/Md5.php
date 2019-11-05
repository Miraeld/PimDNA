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
use \App\Controllers\Utilities;
use \App\Controllers\Technical;
/**
 * Home controller
 *
 * PHP version 7.0
 */
class Md5 extends \Core\Controller
{
    public $listfile = '';
    public $content_folder = array();
    public $utilities;
    /**
     * Show the index page
     *
     * @return void
     */

    public function __construct()
    {

        $this->listfile = '';
        $this->content_folder = array();
        $this->utilities = new Utilities();
        return $this;
    }
    public function indexAction()
    {
      $connexion = new Login();
      $connexion->checkConnexion();

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
    public function index_generation()
    {
      $connexion = new Login();
      $connexion->checkConnexion();

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
        'generation_done' => true


      );
      View::renderTemplate('Md5/index.php', $args);

    }

    public function generate()
    {

      // if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        ignore_user_abort(true);
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        $step_current = 0;
        $step_max = 50;

        $dir = '../..';
        $this->listFolder($dir);

        if ($this->content_folder)
        {
          $total_entries = count($this->content_folder);
          $current_entrie = 0;

          echo json_encode(array('progress' => 0, 'count' => $current_entrie, 'total' => $total_entries)).'--';
          flush();
          ob_flush();


          $dna_file = "../dna/".date('d-m-Y-h-i-s').".pdna";
          $filesave = fopen($dna_file, 'w');

          foreach ($this->content_folder as $index => $info)
          {
            $current_entrie++;
            if (filesize($info['path']) < 2147483648)
            {
              $string =  str_replace('../..','', $info['path'])." || ".md5_file($info['path'])."\n";
              fwrite($filesave, $string);
            }


            $this->content_folder[$index] = null;
            unset($this->content_folder[$index]);
            if ($step_current == round($total_entries/$step_max)) {
                echo json_encode(array('progress' => number_format((($current_entrie/$total_entries)*100), 2), 'count' => $current_entrie, 'total' => $total_entries)).'--';
            } else if ($step_current < round($total_entries/$step_max)) {
              $step_current++;
            } else {
              $step_current = 0;
            }


            // sleep(0.05);
            sleep(0.000166667);
            ob_flush();
            flush();
          }

          fclose($filesave);
          echo json_encode(array('progress' => 100, 'count' => $current_entrie, 'total' => $total_entries)).'--';
          sleep(1);
          ob_flush();
          flush();
          if (Config::HANGOUT_MSG)
          {
            $hangout_msg = "PimDNA Report\n\n";
            $hangout_msg .= "Generation made for ".$_SERVER['SERVER_NAME']." the *".date('d-m-Y')."* at *".date('H:i:s')."*.";
            $this->utilities->send_hg_msg(0, $hangout_msg);
          }
        }
      // }
    }

    public function results()
    {
      $connexion = new Login();
      $connexion->checkConnexion();

      $args = json_decode($_POST['args_data'],true);
      View::renderTemplate('Md5/result.php', $args);
    }


    public function compare()
    {

        // if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        ignore_user_abort(true);
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $current_entrie = 0;
        $current_step = 0;
        $step_max = 50;

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

        $total_entries = count($array_fileline);
        echo json_encode(array('status' => 0, 'progress' => 0, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Init...')).'--';
        flush();
        ob_flush();
        sleep(1);

        foreach ($array_fileline as $index => $value)
        {
          $val = explode(' || ', $value);
          if (!empty($val[0]))
          {
            $existing_index = ($this->search_in_dna($val[0]));

            if ($existing_index)
            {
              if (md5_file($this->content_folder[$existing_index]['path']) == $val[1])
              {
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
          $current_entrie++;

          if ($current_step == round($total_entries/$step_max))
          {
            $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100),2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Comparing...'));
            echo  $dd.'--';
            $current_step++;
          }
          else if ($current_step < round($total_entries/$step_max))
          {
            $current_step++;
          }
          else
          {
            $current_step = 0;
          }


          ob_flush();
          flush();
          // sleep(1);
          sleep(0.000166667);
        }

        $ru = getrusage();
        $time_processed =  $this->utilities->rutime($ru, $rustart, "utime").' ms';

        $file_added = $this->content_folder;
        $technical = new Technical(false);
        $checkdisk = $technical->server_space();



        $current_entrie = 0;
        $total_entries = count($file_added) + count($file_changed);
        echo json_encode(array('status' => 0, 'progress' => 0, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Analyzing...')).'--';

        ob_flush();
        flush();
        sleep(2);
        ////
        //// SUSPICIOUS PROCESS STEP 1
        ////
        $searchforArr = Config::SUSPICIOUS_ARR;
        $suspicious_file = array();
        $current_step = 0;
        foreach ($file_added as $added)
        {
          $contents = file_get_contents($added['path']);
          // foreach ($searchforArr as $searchfor)
          // {
          //   $pattern = preg_quote($searchfor, '/');
          //   $detected = $pattern;
          //   // finalise the regular expression, matching the whole line
          //   $pattern = "/^.*$pattern.*\$/m";
          //   if(preg_match_all($pattern, $contents, $matches))
          //   {
          //     $added['suspicious'] = implode("\n", $matches[0]);
          //     array_push($suspicious_file, $added);
          //     // echo "Files: ".$added['filename']." - Found matches:\n";
          //     // echo implode("\n", $matches[0]);
          //   }
          // }
          $current_entrie++;
          if ($current_step == round($total_entries/$step_max)) {
            $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100),2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Analyzing Added Files...'));
            echo $dd .'--';
            $current_step++;
          } else if ($current_step < round($total_entries/$step_max)) {
            $current_step++;
          } else {
            $current_step = 0;
          }
          ob_flush();
          flush();

          // sleep(0.000166667);
          sleep(0.000166667);
        }
        ////
        //// SUSPICIOUS PROCESS STEP 2
        ////
        $current_step = 0;
        foreach ($file_changed as $modified)
        {
          // $contents = file_get_contents('../..'.$modified['path']);
          // foreach ($searchforArr as $searchfor)
          // {
          //   $pattern = preg_quote($searchfor, '/');
          //   $detected = $pattern;
          //   // finalise the regular expression, matching the whole line
          //   $pattern = "/^.*$pattern.*\$/m";
          //   if(preg_match_all($pattern, $contents, $matches))
          //   {
          //     $modified['suspicious'] = implode("\n", $matches[0]);
          //     array_push($suspicious_file, $modified);
          //
          //    }
          // }
          $current_entrie++;
          if ($current_step == round($total_entries/$step_max)) {
              $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100), 2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Analyzing Modified Files...'));
              echo $dd .'--';
              $current_step++;
          }
          else if ($current_step < round($total_entries/$step_max))
          {
            $current_step++;
          }
          else
          {
            $current_step = 0;
          }

          ob_flush();
          flush();
          sleep(0.000166667);
          // sleep(0.000166667);
        }
        echo json_encode(array('status' => 0, 'progress' => 100, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Preparing results...')).'--';
        ob_flush();
        flush();

        if (Config::SEND_MAIL)
        {
          $mail_result = $this->send_email($file_changed, $file_removed, $file_added, count($file_no_change), false, $checkdisk, $suspicious_file);
        } else {
          $mail_result = false;
        }
        if (Config::HANGOUT_MSG)
        {
          $total_files = count($file_changed) + count($file_removed) + count($file_no_change) + count($file_added);
          $hangout_msg = "PimDNA Comparison for ".$_SERVER['SERVER_NAME']."\n\n";
          $hangout_msg .= "Comparison made the *". date('d-m-Y')."* at *".date('H:i:s')."*\n";
          $hangout_msg .= "```\n";
          $hangout_msg .= " - ". count($file_changed)." Changed Files,\n";
          $hangout_msg .= " - ". count($file_removed)." Removed Files,\n";
          $hangout_msg .= " - ". count($file_added)." Added Files,\n";
          $hangout_msg .= " - ". count($suspicious_file)." Suspicious Detections within modified/added files.\n";
          $hangout_msg .= "``` \n";
          $hangout_msg .= "Total of *".$total_files."* files processed.\n";
          $hangout_msg .= "Report made in *$time_processed*. \n\n";

          $hangout_msg .= "Please refer to the email report for more information.";


          $this->utilities->send_hg_msg(1, $hangout_msg);
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
          'mail_result'           => $mail_result,
          'files_suspicious'      => $suspicious_file,
        );


        echo json_encode(array('status' => 1, 'datas' => $args)) .'--';
        ob_flush();
        flush();

      // }
    }
    public function results_bis() {
      $args = json_decode($_POST['args_data'],true);
      View::renderTemplate('Md5/result.php', $args);

    }

    public function compare_cron()
    {
      if ($_POST['token'] == $this->utilities->getToken())
      {
          ini_set("memory_limit", "-1");
          set_time_limit(0);
          ignore_user_abort(true);
          $error = array('status' => 0, 'message' => 'Success');
          $file_no_change = array();
          $file_changed = array();
          $file_removed = array();

          $rustart = getrusage();

          try {
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

          } catch (Exception $e) {
            $error = array('status' => 1, 'message' => 'An error occured during the Init process');
          }
          // $dir = $_SERVER['DOCUMENT_ROOT'];
          try {
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
              ob_flush();
              flush();
              sleep(0.0005);
            }
          } catch (Exception $e) {
            $error = array('status' => 1, 'message' => 'An error occured during the Compare process');
          }

          try {
            $ru = getrusage();
            $time_processed =  $this->utilities->rutime($ru, $rustart, "utime").' ms';
            $test = $file_changed;
            $file_added = $this->content_folder;
            $technical = new Technical(false);
            $checkdisk = $technical->server_space();

            $searchforArr = Config::SUSPICIOUS_ARR;
            $suspicious_file = array();
            foreach ($file_added as $added)
            {
              // $contents = file_get_contents($added['path']);
              // foreach ($searchforArr as $searchfor)
              // {
              //   $pattern = preg_quote($searchfor, '/');
              //   $detected = $pattern;
              //   // finalise the regular expression, matching the whole line
              //   $pattern = "/^.*$pattern.*\$/m";
              //   if(preg_match_all($pattern, $contents, $matches))
              //   {
              //     $added['suspicious'] = implode("\n", $matches[0]);
              //     array_push($suspicious_file, $added);
              //     // echo "Files: ".$added['filename']." - Found matches:\n";
              //     // echo implode("\n", $matches[0]);
              //   }
              // }
              ob_flush();
              flush();
              sleep(0.000166667);
            }

            foreach ($file_changed as $modified)
            {
              // $contents = file_get_contents('../..'.$modified['path']);
              // foreach ($searchforArr as $searchfor)
              // {
              //   $pattern = preg_quote($searchfor, '/');
              //   $detected = $pattern;
              //   // finalise the regular expression, matching the whole line
              //   $pattern = "/^.*$pattern.*\$/m";
              //   if(preg_match_all($pattern, $contents, $matches))
              //   {
              //     $modified['suspicious'] = implode("\n", $matches[0]);
              //     array_push($suspicious_file, $modified);
              //
              //   }
              // }
              ob_flush();
              flush();
              sleep(0.000166667);
            }
          } catch (Exception $e) {
            $error = array('status' => 1, 'message' => 'An error occured during the Analyzing process');
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
            if ($error['status'] == 0) {
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

            }
            else if ($error['status'] == 1)
            {
              $hangout_msg = "PimDNA *ERROR* Comparison for ".$_SERVER['SERVER_NAME']."\n\n";
              $hangout_msg .= $error['message']."\n";
            }

            $this->utilities->send_hg_msg(1, $hangout_msg);
          }

          echo json_encode($error);

          return true;


      }

    }

    public function generate_cron()
    {

      if ($_POST['token'] == $this->utilities->getToken())
      {
        ignore_user_abort(true);
        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $dir = '../..';
        $this->listFolder($dir);

        if ($this->content_folder) {
          try {
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
              ob_flush();
              flush();
              sleep(0.000166667);

            }
            fclose($filesave);
            $error  = false;
          } catch (Exception $e) {
            $error = true;
          }


        }
        ob_flush();
        flush();

        // var_export();
        if (Config::SEND_MAIL)
        {
            $this->send_generate_email($_POST['contact'], $filename_dna);
        }

        if (Config::HANGOUT_MSG)
        {
          if ($error) {
            $hangout_msg = "PimDNA Report\n\n";
            $hangout_msg .= "An error has occured while generating the PDNA file for *".$_SERVER['SERVER_NAME']."* the *".date('d-m-Y')."* at *".date('H:i:s')."*.";
          } else {
            $hangout_msg = "PimDNA Report\n\n";
            $hangout_msg .= "Generation made for ".$_SERVER['SERVER_NAME']." the *".date('d-m-Y')."* at *".date('H:i:s')."*.";

          }
          $this->utilities->send_hg_msg(0, $hangout_msg);
        }

        if ($error) {
          echo json_encode(array('status' => 0, 'message' => 'An error happened'));
        } else {
          echo json_encode(array('status' => 1, 'message' => 'Success'));
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
        $message .= '<p><span style="font-weight:bold;">Total Disk Space:</span> '.$this->utilities->formatBytes($checkdisk['total_space']).' </p>';
        $message .= '<p><span style="font-weight:bold;">Disk Free:</span> '.$this->utilities->formatBytes($checkdisk['free_space']).' </p>';
        $message .= '<p><span style="font-weight:bold;">Disk Overview:</span> '.(number_format($checkdisk['percent_space'], 2)).'% used </p>';
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
        flush();
        ob_flush();
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

    public function test_bis() {
      if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

          $total = 25;
          $i = 0;

          echo json_encode(array('progress' => 0, 'count' => $i, 'total' => $total));
          flush();
          ob_flush();

          while ($i < $total) {
              $i++;
              echo json_encode(array('progress' => (($i/$total)*100), 'count' => $i, 'total' => $total));
              flush();
              ob_flush();
              sleep(1);
          }
          exit();
      }
    }

    public function test_one()
    {
      for ($j = 0; $j < 10; $j++) {
        $rustart = getrusage();
        $filename_dna ="../dna/"."test_one-$j".".pdna";
        $filesave = fopen($filename_dna, 'w');
        for ($i = 0; $i < 10000; $i++)
        {


            $string =  $i;
            fwrite($filesave, $string."\n");
            sleep(0.000166667);

        }
        fclose($filesave);
        $ru = getrusage();
        echo "This process used " . $this->utilities->rutime($ru, $rustart, "utime") ." ms for its computations<br>";
      }
    }

    public function test_two()
    {
      for ($j = 0; $j < 10; $j++) {
        $rustart = getrusage();


        $filename_dna ="../dna/"."test_two-$j".".pdna";
        for ($i = 0; $i < 10000; $i++)
        {
          $string =  $i."\r\n";
          file_put_contents($filename_dna, $string , FILE_APPEND | LOCK_EX);
          sleep(0.000166667);

        }
        $ru = getrusage();
        echo "This process used " . $this->utilities->rutime($ru, $rustart, "utime") ." ms for its computations<br>";
      }
    }


    /**
     * COMPARE TEST PERFORMANCE
     * @return [type] [description]
     */
    public function compare_test()
    {
        $rustart_all = getrusage();
        // if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        ignore_user_abort(true);
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $current_entrie = 0;
        $current_step = 0;
        $step_max = 50;

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

        $total_entries = count($array_fileline);
        echo json_encode(array('status' => 0, 'progress' => 0, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Init...')).'--';
        flush();
        ob_flush();

        foreach ($array_fileline as $index => $value)
        {
          $val = explode(' || ', $value);
          if (!empty($val[0]))
          {
            $existing_index = ($this->search_in_dna($val[0]));

            if ($existing_index)
            {
              if (md5_file($this->content_folder[$existing_index]['path']) == $val[1])
              {
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
          $current_entrie++;
          if ($current_step == round($total_entries/$step_max))
          {

            $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100),2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Comparing...'));
            echo  $dd.'--';

            $current_step++;
          }
          else if ($current_step < round($total_entries/$step_max))
          {
            $current_step++;
          }
          else
          {
            $current_step = 0;
          }

          ob_flush();
          flush();
          // sleep(1);
          sleep(0.000166667);
        }

        $ru = getrusage();
        $time_processed =  $this->utilities->rutime($ru, $rustart, "utime").' ms';

        $file_added = $this->content_folder;

        $current_entrie = 0;
        $total_entries = count($file_added) + count($file_changed);
        echo json_encode(array('status' => 0, 'progress' => 0, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Analyzing...')).'--';
        sleep(2);
        ob_flush();
        flush();
        ////
        //// SUSPICIOUS PROCESS STEP 1
        ////
        $searchforArr = Config::SUSPICIOUS_ARR;
        $suspicious_file = array();
        $current_step = 0;
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
              // $added['suspicious'] = implode("\n", $matches[0]);
              $added['suspicious'] = $detected;
              array_push($suspicious_file, $added);
              // echo "Files: ".$added['filename']." - Found matches:\n";
              // echo implode("\n", $matches[0]);
            }
          }
          $current_entrie++;
          if ($current_step == round($total_entries/$step_max)) {
            $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100),2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Analyzing Added Files...'));
            echo $dd .'--';

            $current_step++;
          } else if ($current_step < round($total_entries/$step_max)) {
            $current_step++;
          } else {
            $current_step = 0;
          }
          ob_flush();
          flush();

          // sleep(0.000166667);
          sleep(0.000166667);
        }
        ////
        //// SUSPICIOUS PROCESS STEP 2
        ////
        $current_step = 0;
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
              $modified['suspicious'] = $detected;
              // $modified['suspicious'] = implode("\n", $matches[0]);
              array_push($suspicious_file, $modified);

             }
          }
          $current_entrie++;
          if ($current_step == round($total_entries/$step_max)) {
              $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100), 2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Analyzing Modified Files...'));
              echo $dd .'--';
              $current_step++;

          }
          else if ($current_step < round($total_entries/$step_max))
          {
            $current_step++;
          }
          else
          {
            $current_step = 0;
          }

          ob_flush();
          flush();
          sleep(0.000166667);
          // sleep(0.000166667);
        }

        $technical = new Technical(false);
        $checkdisk = $technical->server_space();
        echo json_encode(array('status' => 0, 'progress' => 100, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Preparing results...')).'--';
        sleep(1);
        ob_flush();
        flush();

        if (Config::SEND_MAIL)
        {
          $mail_result = $this->send_email($file_changed, $file_removed, $file_added, count($file_no_change), false, $checkdisk, $suspicious_file);
        } else {
          $mail_result = false;
        }
        if (Config::HANGOUT_MSG)
        {
          $total_files = count($file_changed) + count($file_removed) + count($file_no_change) + count($file_added);
          $hangout_msg = "PimDNA Comparison for ".$_SERVER['SERVER_NAME']."\n\n";
          $hangout_msg .= "Comparison made the *". date('d-m-Y')."* at *".date('H:i:s')."*\n";
          $hangout_msg .= "```\n";
          $hangout_msg .= " - ". count($file_changed)." Changed Files,\n";
          $hangout_msg .= " - ". count($file_removed)." Removed Files,\n";
          $hangout_msg .= " - ". count($file_added)." Added Files,\n";
          $hangout_msg .= " - ". count($suspicious_file)." Suspicious Detections within modified/added files.\n";
          $hangout_msg .= "``` \n";
          $hangout_msg .= "Total of *".$total_files."* files processed.\n";
          $hangout_msg .= "Report made in *$time_processed*. \n\n";

          $hangout_msg .= "Please refer to the email report for more information.";


          $this->utilities->send_hg_msg(1, $hangout_msg);
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
          'mail_result'           => $mail_result,
          'files_suspicious'      => $suspicious_file,
        );


        echo json_encode(array('status' => 1, 'datas' => $args)) .'--';
        ob_flush();
        flush();

        $ru_all = getrusage();
        echo "<hr>";
        echo "This process used " . $this->utilities->rutime($ru_all, $rustart_all, "utime") ." ms for its computations<br>";

      // }
    }


    public function compare_init()
    {
      ignore_user_abort(true);
      ini_set("memory_limit", "-1");
      set_time_limit(0);

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

      echo json_encode(array('error' => false, 'dna_array' => $array_fileline, 'content_folder' => $this->content_folder));
    }

    public function compare_compare()
    {
      ignore_user_abort(true);
      ini_set("memory_limit", "-1");
      set_time_limit(0);

      $file_no_change = array();
      $file_changed = array();
      $file_removed = array();

      $current_entrie = 0;
      $current_step = 0;
      $step_max = 50;

      if (isset($_POST['dna']) && isset($_POST['folder']))
      {
        $dna_array = json_decode($_POST['dna'],true);
        $this->content_folder = json_decode($_POST['folder'], true);
        $total_entries = count($dna_array);
        echo json_encode(array('status' => 0, 'progress' => 0, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Comparing...')).'--';
        flush();
        ob_flush();
        sleep(1);
        foreach ($dna_array as $index => $value)
        {
          $val = explode(' || ', $value);
          if (!empty($val[0]))
          {
            $existing_index = ($this->search_in_dna($val[0]));

            if ($existing_index)
            {
              if (md5_file($this->content_folder[$existing_index]['path']) == $val[1])
              {
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
          $current_entrie++;

          if ($current_step == round($total_entries/$step_max))
          {
            $dd = json_encode(array('status' => 0, 'progress' => number_format((($current_entrie/$total_entries)*100),2), 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Comparing...'));
            echo  $dd.'--';
            $current_step++;
          }
          else if ($current_step < round($total_entries/$step_max))
          {
            $current_step++;
          }
          else
          {
            $current_step = 0;
          }

          ob_flush();
          flush();
          // sleep(1);
          sleep(0.000166667);
        }
        $dd = json_encode(array('status' => 0, 'progress' => 100, 'count' => $total_entries, 'total' => $total_entries, 'type' => 'Comparing...'));
        echo  $dd.'--';

        $file_added = $this->content_folder;
        ob_flush();
        flush();
        echo json_encode(array(
          'status'          => 1,
          'error'           => false,
          'file_added'      => $file_added,
          'file_no_change'  => $file_no_change,
          'file_changed'    => $file_changed,
          'file_removed'    => $file_removed
        )).'--';

      } else {
        echo json_encode(array('status' => 0, 'error'=> true, 'msg' => "Pas de POST value"));
      }
    }

    public function compare_analyze()
    {
      ignore_user_abort(true);
      ini_set("memory_limit", "-1");
      set_time_limit(0);
      if (isset($_POST['file_added']) && isset($_POST['file_no_change']) && isset($_POST['file_changed']) && isset($_POST['file_removed']))
      {
        $file_added = json_decode($_POST['file_added'], true);
        $file_no_change = json_decode($_POST['file_no_change'], true);
        $file_changed = json_decode($_POST['file_changed'], true);
        $file_removed = json_decode($_POST['file_removed'], true);


        $searchforArr = Config::SUSPICIOUS_ARR;
        $suspicious_file = array();
        $current_step = 0;
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
              // $added['suspicious'] = implode("\n", $matches[0]);
              $added['suspicious'] = $detected;
              array_push($suspicious_file, $added);
              // echo "Files: ".$added['filename']." - Found matches:\n";
              // echo implode("\n", $matches[0]);
            }
          }

          ob_flush();
          flush();

          // sleep(0.000166667);
          sleep(0.000166667);
        }
        ////
        //// SUSPICIOUS PROCESS STEP 2
        ////
        $current_step = 0;
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
              $modified['suspicious'] = $detected;
              // $modified['suspicious'] = implode("\n", $matches[0]);
              array_push($suspicious_file, $modified);

             }
          }


          ob_flush();
          flush();
          sleep(0.000166667);
          // sleep(0.000166667);
        }
      }
      echo json_encode(array(
        'error'           => false,
        'file_added'      => $file_added,
        'file_no_change'  => $file_no_change,
        'file_changed'    => $file_changed,
        'file_removed'    => $file_removed,
        'suspicious_file' => $suspicious_file
      ));

    }
    public function compare_finalyze()
    {
      ignore_user_abort(true);
      ini_set("memory_limit", "-1");
      set_time_limit(0);
        if (isset($_POST))
        {
          $time_processed = $_POST['time_processed'];
          // init
          $file_added = json_decode($_POST['file_added'], true);
          $file_no_change = json_decode($_POST['file_no_change'], true);
          $file_changed = json_decode($_POST['file_changed'], true);
          $file_removed = json_decode($_POST['file_removed'], true);
          $suspicious_file = json_decode($_POST['suspicious_file'], true);

          // end init
          $technical = new Technical(false);
          $checkdisk = $technical->server_space();
          // echo json_encode(array('status' => 0, 'progress' => 100, 'count' => $current_entrie, 'total' => $total_entries, 'type' => 'Preparing results...')).'--';
          // sleep(1);
          ob_flush();
          flush();

          if (Config::SEND_MAIL)
          {
            $mail_result = $this->send_email($file_changed, $file_removed, $file_added, count($file_no_change), false, $checkdisk, $suspicious_file);
          } else {
            $mail_result = false;
          }
          if (Config::HANGOUT_MSG)
          {
            $total_files = count($file_changed) + count($file_removed) + count($file_no_change) + count($file_added);
            $hangout_msg = "PimDNA Comparison for ".$_SERVER['SERVER_NAME']."\n\n";
            $hangout_msg .= "Comparison made the *". date('d-m-Y')."* at *".date('H:i:s')."*\n";
            $hangout_msg .= "```\n";
            $hangout_msg .= " - ". count($file_changed)." Changed Files,\n";
            $hangout_msg .= " - ". count($file_removed)." Removed Files,\n";
            $hangout_msg .= " - ". count($file_added)." Added Files,\n";
            $hangout_msg .= " - ". count($suspicious_file)." Suspicious Detections within modified/added files.\n";
            $hangout_msg .= "``` \n";
            $hangout_msg .= "Total of *".$total_files."* files processed.\n";
            $hangout_msg .= "Report made in *$time_processed*. \n\n";

            $hangout_msg .= "Please refer to the email report for more information.";


            $this->utilities->send_hg_msg(1, $hangout_msg);
          }

          $args = array(
            'error'                 => false,
            'file_changed_count'    => count($file_changed),
            'file_modified'         => $file_changed,
            'file_removed_count'    => count($file_removed),
            'file_removed'          => $file_removed,
            'file_no_change_count'  => count($file_no_change),
            'time_processed'        => $time_processed,
            'file_added_count'      => count($file_added),
            'file_added'            => $file_added,
            'mail_result'           => $mail_result,
            'files_suspicious'      => $suspicious_file,
          );
          echo json_encode(array('status' => 1, 'datas' => $args));
        }
    }
}
