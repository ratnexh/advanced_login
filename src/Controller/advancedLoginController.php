<?php

/**
 * @file
 * Contains \Drupal\advanced_login\GoogleLoginController.
 */

/**
 * Controller for the Google Login functionality.
 */
namespace Drupal\advanced_login\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;
use Drupal\Core\Password\PasswordGenerator;

/**
 * Class GoogleLoginController.
 *
 * @package Drupal\advanced_login\Controller
 */
class advancedLoginController extends ControllerBase {

  /**
   * Google Login page callback.
   */
  public function login() {
    $redirectUri = \Drupal::config('advanced_login_config_form.setting')->get('redirect_uri');
    $clientId = \Drupal::config('advanced_login_config_form.setting')->get('client_id');

    $redirect_url = 'https://accounts.google.com/o/oauth2/auth';
    $query = [
      'client_id' => $clientId,
      'redirect_uri' => $redirectUri,
      'scope' => 'https://www.googleapis.com/auth/userinfo.email',
      'response_type' => 'code',
    ];
    $redirect_url .= '?' . http_build_query($query);
    $response = new TrustedRedirectResponse($redirect_url);
    return $response;
  }


  public function advanced_login_oauth_callback() {
    // Check if the OAuth2 state matches what you sent in the initial request for security.
    $state = isset($_GET['state']) ? $_GET['state'] : '';
    if (isset($_SESSION['oauth_state']) && $state !== $_SESSION['oauth_state']) {
      $this->messenger()->addError('OAuth2 state mismatch. Possible security threat.');
      return $this->redirect('<front>');
    }
    // \Drupal::logger('state')->warning('<pre>'.print_r($state,true).'</pre>');
    // Check if the OAuth2 response contains an error.
    if (isset($_GET['error'])) {
      $this->messenger()->addError('OAuth2 authentication failed: ' . $_GET['error_description']);
      return $this->redirect('<front>');
    }
  
    // If there are no errors, proceed to exchange the authorization code for an access token.
    if (isset($_GET['code'])) {
      // Perform the token exchange with Google's OAuth2 server. This code depends on your implementation.
      // You'll need to make an HTTP request to exchange the code for an access token.
      $access_token = $this->advanced_login_exchange_code($_GET['code']);
  
      // Use the access token to fetch user data from Google.
      $user_info = $this->advanced_login_fetch_user_info($access_token);
      // \Drupal::logger('user_info')->warning('<pre>'.print_r($user_info,true).'</pre>');
  
      if ($user_info) {
        // Check if the user with this email already exists in Drupal.
        $email = $user_info['email'];
        $existing_user = user_load_by_mail($email);
     
        if ($existing_user) {
          // Log in the existing user.
          user_login_finalize($existing_user);
        } else {
          // Create a new Drupal user.
          $pas = $this->generatePassword(12);
          $this->messenger()->addMessage('Password: '.$pas);
          \Drupal::logger('Password')->warning('<pre>'.print_r($pas,true).'</pre>');
          $new_user = User::create();
          $new_user->setPassword($pas);
          $new_user->enforceIsNew();
          $new_user->setEmail($email);
          $new_user->setUsername($user_info['email']);
          // $new_user->addRole('anonymous');
          $new_user->activate();
          $new_user->save();
  
          if ($new_user) {
            // Log in the newly created user.
            user_login_finalize($new_user);
          } else {
            $this->messenger()->addError('Error creating a new user.');
          }
        }
      } else {
        throw new \Exception('Exception: Failed to fetch user data from Google.');
      }
    }
  
    // Redirect the user to a specific page after successful login.
    return $this->redirect('<front>');
  }
  
  
  // Function to exchange the authorization code for an access token with Google's OAuth2 server.
  public function advanced_login_exchange_code($code) {
    $redirectUri = \Drupal::config('advanced_login_config_form.setting')->get('redirect_uri');
    $clientId = \Drupal::config('advanced_login_config_form.setting')->get('client_id');
    $clientSecret = \Drupal::config('advanced_login_config_form.setting')->get('client_secret');
    // Set the request headers.
    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
    ];
  
    // Set the request body.
    $form_params = [
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
      'code' => $code,
      'grant_type' => 'authorization_code',
      'redirect_uri' => $redirectUri,
    ];
  
    // Make an HTTP POST request to Google's token endpoint.
    $response = \Drupal::httpClient()->post('https://oauth2.googleapis.com/token', [
      'headers' => $headers,
      'form_params' => $form_params,
    ]);
  
    // Parse the response and extract the access token.
    $response_data = json_decode($response->getBody(), true);
    $access_token = $response_data['access_token'];
  
    // Return the access token.
    return $access_token;
  }
  
  
  // Function to fetch user data from Google using the access token.
  public function advanced_login_fetch_user_info($access_token) {
    // Set the request headers.
    $headers = [
      'Authorization' => 'Bearer ' . $access_token,
    ];
  
    // Make an HTTP GET request to the Google API.
    $response = \Drupal::httpClient()->get('https://www.googleapis.com/oauth2/v1/userinfo', [
      'headers' => $headers,
    ]);
  
    // Parse the response and extract the user's data.
    $user_info = json_decode($response->getBody(), true);
    \Drupal::logger('User Info')->warning('<pre>'.print_r($user_info,true).'</pre>');
    // Return the user data as an array.
    return [
      'email' => $user_info['email'],
    ];
  }
  function generatePassword($length = 12) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
    $password = "";
    for ($i = 0; $i < $length; $i++) {
      $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
  }
}

