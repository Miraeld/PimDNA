<?php
/**
  * @author: Gaël Robin - Pimclick
  * @version: 1.1.0
  *
**/
namespace App\Controllers;

use \Core\View;
use \App\Config;
use \App\Controllers\Login;
use \App\Controllers\Utilities;


/**
 * Settings controller
 *
 * PHP version 7.0
 */
class Settings extends \Core\Controller
{

  public function __construct()
  {
    $connexion = new Login();
    $connexion->checkConnexion();

  }

  public function index()
  {
    $utilities = new Utilities();
    $token = $utilities->getToken();

    $args = array('token' => $token);
    View::renderTemplate('Settings/index.php', $args);
  }

}
