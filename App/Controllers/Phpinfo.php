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
class Phpinfo extends \Core\Controller
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

      ob_start();
      phpinfo();
      $phpinfo = ob_get_clean();


      View::renderTemplate('Phpinfo/index.php', array('phpinfo' => $phpinfo));

    }

}
