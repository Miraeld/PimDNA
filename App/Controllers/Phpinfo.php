<?php
/**
  * @author: Gaël Robin
  *
**/
namespace App\Controllers;

use \Core\View;
use \App\Config;


/**
 * Home controller
 *
 * PHP version 7.0
 */
class Phpinfo extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
      $this->checkConnexion();
      ob_start();
      phpinfo();
      $phpinfo = ob_get_clean();


      View::renderTemplate('Phpinfo/index.php', array('phpinfo' => $phpinfo));
      // echo $twig->render('phpinfo.html.twig', array('phpinfo' => $phpinfo));
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
