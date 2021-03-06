<?php
/**
  * @author: Gaël Robin
  *
**/

namespace App\Controllers;

use \Core\View;
use \App\Config;

use \App\Controllers\Login;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Delete extends \Core\Controller
{
  public $list_all_folders = array();



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
    public function index()
    {

      $this->list_all_dir('../..');
      $args = array(
        'list_all_dir' => $this->list_all_folders,
      );
      View::renderTemplate('Delete/index.php', $args);
    }


    private function list_all_dir($dir)
    {
      $tmp_content = scandir($dir);
      $tmp_list_file = array();
      unset($tmp_content[array_search('.', $tmp_content, true)]);
      unset($tmp_content[array_search('..', $tmp_content, true)]);
      foreach ($tmp_content as $file)
      {

        if (is_dir($dir.'/'.$file)) {
            array_push($this->list_all_folders, array('filename' => $dir.'/'.$file, 'path' => str_replace('../..', '', $dir.'/'.$file)));
            $this->list_all_dir($dir.'/'.$file);

        }
        else
        {

        }
      }
    }
    public function del()
    {
        $this->delete_files('../../Helloworld');
        // $this->delete_files($_POST['path']);

    }

    private function deleteDirectory($dirPath)
    {

        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object !="..") {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                        deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
        reset($objects);
        rmdir($dirPath);
        }
    }

    private function delete_files($target) {


      if (PHP_OS == 'Linux') {
            exec('chmod -R 777 '.$target);
            exec('rm -rf '.$target);
      } else {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

            foreach( $files as $file ){
                $this->delete_files( $file );
            }

            rmdir( $target );
        } elseif(is_file($target)) {
              chmod($target, 777);
              unlink( $target );

        }
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
}
