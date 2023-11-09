<?php

namespace Drupal\advanced_login\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class advancedLoginConfigForm extends ConfigFormBase{

    public function getFormId() {
        return 'advanced_login_config_form';
    }

    protected function getEditableConfigNames() {
        return ['advanced_login_config_form.setting'];
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('advanced_login_config_form.setting');
        
        $form['display_settings'] = [
            '#type' => 'details',
            '#title' => $this->t('Display Settings'),
            '#open' => TRUE,
            'show_users' =>[
                '#type' => 'checkbox',
                '#title' => 'Show list of Existing users on Login Page',
                '#default_value' => $config->get('show_users'),
            ],
            'show_login_with_google_btn'=>[
                '#type' => 'checkbox',
                '#title' => "Show 'Login with google' button",
                '#default_value' => $config->get('show_login_with_google_btn'),
            ],
            'change_login_btn_display'=>[
                '#type' => 'details',
                '#title' => $this->t("Change 'Log in' button display"),
                '#open' => FALSE,
                'change_login_btn_text'=>[
                    '#type' => 'textfield',
                    '#title' => "Change text",
                    '#default_value' => $config->get('change_login_btn_text'),
                ],
                'change_login_btn_text_color'=>[
                    '#type' => 'color',
                    '#title' => "Change text color",
                    '#default_value' => $config->get('change_login_btn_text_color'),
                ],
                'change_login_btn_color'=>[
                    '#type' => 'color',
                    '#title' => "Change background color",
                    '#default_value' => $config->get('change_login_btn_color'),
                ],
            ],
            'change_login_ggl_btn_display'=>[
                '#type' => 'details',
                '#title' => $this->t("Change 'Login with Google' button display"),
                '#open' => FALSE,
                'change_login_ggl_btn_text'=>[
                    '#type' => 'textfield',
                    '#title' => "Change text",
                    '#default_value' => $config->get('change_login_ggl_btn_text'),
                ],
                'change_login_ggl_btn_text_color'=>[
                    '#type' => 'color',
                    '#title' => "Change text color",
                    '#default_value' => $config->get('change_login_ggl_btn_text_color'),
                ],
                'change_login_ggl_btn_color'=>[
                    '#type' => 'color',
                    '#title' => "Change background color",
                    '#default_value' => $config->get('change_login_ggl_btn_color'),
                ],
            ]
        ];
        $form['advanced_login_details'] = [
            '#type' => 'details',
            '#title' => $this->t('Google Login Details'),
            '#open' => FALSE,
            'client_id' => [
                '#type' => 'textfield',
                '#title' => 'Client id: ',
                '#default_value' => $config->get('client_id'),
            ],
            'client_secret' => [
                '#type' => 'textfield',
                '#title' => 'Client secret: ',
                '#default_value' => $config->get('client_secret'),
            ],
            'redirect_uri' => [
                '#type' => 'textfield',
                '#title' => 'Redirect uri: ',
                '#default_value' => $config->get('redirect_uri'),
            ],
        ];
        return parent::buildForm($form, $form_state);
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        drupal_flush_all_caches();
        $this->config('advanced_login_config_form.setting')
        ->set('client_id', $form_state->getValue('client_id'))
        ->set('client_secret', $form_state->getValue('client_secret'))
        ->set('redirect_uri', $form_state->getValue('redirect_uri'))
        ->set('show_users', $form_state->getValue('show_users'))
        ->set('show_login_with_google_btn', $form_state->getValue('show_login_with_google_btn'))
        ->set('change_login_btn_text', $form_state->getValue('change_login_btn_text'))
        ->set('change_login_btn_text_color', $form_state->getValue('change_login_btn_text_color'))
        ->set('change_login_btn_color', $form_state->getValue('change_login_btn_color'))
        ->set('change_login_ggl_btn_text', $form_state->getValue('change_login_ggl_btn_text'))
        ->set('change_login_ggl_btn_text_color', $form_state->getValue('change_login_ggl_btn_text_color'))
        ->set('change_login_ggl_btn_color', $form_state->getValue('change_login_ggl_btn_color'))
        ->save();
        parent::submitForm($form, $form_state);
        
    }
}