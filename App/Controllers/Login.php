<?php
/**
 * @author: Gael Robin - Pimclick
 * @version: 1.1.0
 *
 */
  namespace App\Controllers;
  use \Core\View;
  use \App\Config;
  use \App\Controllers\Misc;

  /**
   * Login controller
   */
  class Login extends \Core\Controller
  {

    public function __construct()
    {
      return $this;
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
          header('Location: /pimdna/public');
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

    public function login()
    {

      if (!isset($_SESSION) || empty($_SESSION))
      {
          View::renderTemplate('Home/login.php');

      } else {
        header("Location: /pimdna/public/home");
      }

    }

  }
