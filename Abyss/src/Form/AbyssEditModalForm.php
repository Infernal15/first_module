<?php

namespace Drupal\Abyss\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Test module.
 */
class AbyssEditModalForm extends FormBase {

  /**
   * The name of the table in which
   * the recorded information from this form is stored.
   *
   * @var string
   */
  protected string $table;

  /**
   * @var array
   * Saves validation conditions for avatar.
   */
  protected array $avatar_valid;

  /**
   * @var array
   * Saves validation conditions for response_image.
   */
  protected array $response_image_valid;

  /**
   * @var int
   * Contain id form in Database when form editing.
   */
  protected int $id;

  /**
   * @var string
   * Contain timestamp value when form editing.
   */
  protected string $data;

  /**
   * {@inheritdoc}
   *
   * Constructs a new AbyssEditModalForm.
   *
   * @var array avatar_valid parammeter for validation managed_file
   * in avatar field.
   * @var array response_image_valid parammeter for validation managed_file
   * in response_image field.
   * @var string table contains table name for Abyss module.
   * @var int id contain row id and add correct value in buildForm.
   */
  public function __construct() {
    $this->avatar_valid = [
      'file_validate_is_image'      => [],
      'file_validate_extensions'    => ['jpeg jpg png'],
      'file_validate_size'          => [2097152],
    ];
    $this->response_image_valid = [
      'file_validate_is_image'      => [],
      'file_validate_extensions'    => ['jpeg jpg png'],
      'file_validate_size'          => [5242880],
    ];
    $this->table = 'Abyss';
    $this->id = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    // @todo Implement getFormId() method.
    return 'abyss_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $id = 0): array {
    // @todo Implement buildForm() method.
    $fields = NULL;
    $this->id = $id;
    if ($id === 0) {
      if (\Drupal::database()->schema()->tableExists($this->table)) {
        $query = \Drupal::database()->select($this->table, 'ab');
        $query->addField('ab', 'id');
        $query->orderBy('id', 'DESC');
        $id = $query->execute()->fetchField() + 1;
      }
    }
    else {
      if (\Drupal::database()->schema()->tableExists($this->table)) {
        $query = \Drupal::database()->select($this->table, 'ab');
        $query->fields('ab', [
          'name',
          'email',
          'phone',
          'response',
          'avatar',
          'response_image',
          'data',
        ]);
        $query->condition('id', $id, '=');
        $fields = $query->execute()->fetch();
        $this->data = $fields->data;
      }
    }

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result"></div>',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => !$fields ?: $fields->name,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email'),
      '#default_value' => !$fields ?: $fields->email,
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#title' => $this->t('phone'),
      '#type' => 'tel',
      '#default_value' => !$fields ?: $fields->phone,
      '#description' => $this->t('Start with + and your country code.'),
      '#required' => TRUE,
    ];

    $form['response'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Response'),
      '#default_value' => !$fields ? '' : $fields->response,
      '#required' => TRUE,
    ];

    $form['avatar'] = [
      '#title' => $this->t('Upload avatar image.'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://abyss/' . $id . '/',
      '#multiple'             => FALSE,
      '#description'          => $this->t('Allowed extensions: jpeg jpg png'),
      '#upload_validators'    => $this->avatar_valid,
      '#progress_indicator' => 'bar',
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value' => [!$fields ? '-1' : $fields->avatar],
    ];

    $form['response_image'] = [
      '#title' => $this->t('Upload image for response.'),
      '#type' => 'managed_file',
      '#upload_location' => 'public://abyss/' . $id . '/',
      '#multiple'             => FALSE,
      '#description'          => $this->t('Allowed extensions: jpeg jpg png'),
      '#upload_validators'    => $this->response_image_valid,
      '#progress_indicator' => 'bar',
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#default_value' => [!$fields ? '-1' : $fields->response_image],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->id !== 0 ? $this->t('Save form') : $this->t('Send form'),
      '#ajax' => [
        'callback' => '::sendForm',
        'event' => 'click',
      ],
    ];

    if ($this->id !== 0) {
      $form['cancel'] = [
        '#type' => 'button',
        '#value' => $this->t('Cancel'),
        '#ajax' => [
          'callback' => '::cancelForm',
          'event' => 'click',
        ],
      ];
    }

    return $form;
  }

  /**
   * Callback for AjaxForm.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *
   *   {@inheritdoc}
   *   Delete modal form when user push close button.
   */
  public function cancelForm(array &$form, FormStateInterface &$form_state): AjaxResponse {
    $response = new AjaxResponse();

    $response->addCommand(new RemoveCommand('[id*="abyss-form-"]'));
    $response->addCommand(new RemoveCommand('[id*="drupal-dialog-abyssform"]'));
    $response->addCommand(new RemoveCommand('.ui-dialog'));

    return $response;
  }

  /**
   * {@inheritdoc}
   * Sequential form validation function.
   * Completes its execution when an error is found.
   * The error is further output thanks to the sendForm function.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $len = mb_strlen($form_state->getValue('name'));
    if ($len < 2 || $len > 100) {
      if ($len < 2) {
        $form_state->setErrorByName('name', $this->t('The name is too short.'));
      }
      else {
        $form_state->setErrorByName('name', $this->t('The name is too long.'));
      }
      return;
    }

    if (!\Drupal::service('email.validator')->isValid($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('Invalid email.'));
      return;
    }

    $phone = $form_state->getValue('phone');
    $phone = str_replace(' ', '', $phone);
    $phone = str_replace('+', '', $phone);
    $phone = str_replace('-', '', $phone);
    $phone = str_replace('(', '', $phone);
    $phone = str_replace(')', '', $phone);
    $phone = str_replace('[', '', $phone);
    $phone = str_replace(']', '', $phone);
    $phone = str_replace('{', '', $phone);
    $phone = str_replace('}', '', $phone);

    if (!preg_match('/^\d[\d\(\)\ -]{4,10}\d$/', $phone)) {
      $phone_size = mb_strlen($phone);
      if ($phone_size < 12) {
        $form_state->setErrorByName('phone', $this->t('Phone number is too short.'));
      }
      elseif ($phone_size > 12) {
        $form_state->setErrorByName('phone', $this->t('Phone number is too long.'));
      }
      else {
        $form_state->setErrorByName('phone', $this->t('Phone is incorrect.'));
      }
      return;
    }
    else {
      $form_state->setValue('phone', $phone);
    }

    $len = mb_strlen($form_state->getValue('response'));
    if ($len < 1) {
      $form_state->setErrorByName('response', $this->t('Response is require.'));
      return;
    }
  }

  /**
   * Callback for AjaxForm.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *
   *
   *   {@inheritdoc}
   *   Displays information about the save status.
   */
  public function sendForm(array &$form, FormStateInterface &$form_state): AjaxResponse {
    $response = new AjaxResponse();

    foreach ($form_state->getErrors() as $error) {
      $response->addCommand(new MessageCommand($error, '.result', ['type' => 'error']));
    }

    if (count($form_state->getErrors()) === 0) {
      if ($this->id !== 0) {
        $response->addCommand(new MessageCommand('Form successfully edited.', '.result', ['type' => 'status']));
        $response->addCommand(new RemoveCommand('[id*="abyss-form-"]'));
        $response->addCommand(new RemoveCommand('[id*="drupal-dialog-abyssform"]'));
        $response->addCommand(new RemoveCommand('.ui-dialog'));
      }
      else {
        $response->addCommand(new InvokeCommand('#abyss-form', 'trigger', ['reset']));
        $response->addCommand(new RemoveCommand('form[data-drupal-selector="abyss-form"]'));
        $response->addCommand(new RemoveCommand('.abyss-form'));
        $response->addCommand(new MessageCommand('The form was successfully created.', '.result', ['type' => 'status']));
      }
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   * Saves files, saves, or modifies information in the Abyss Database table.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Implement submitForm() method.
    $database = \Drupal::database();

    $avatar = NULL;
    $image = $form_state->getValue('avatar');
    $file = File::load($image[0]);
    if (!empty($file)) {
      $file->setPermanent();
      $file->save();
      unset($file);
      $avatar = $image[0];
      unset($image);
    }

    $response_image = NULL;
    $image = $form_state->getValue('response_image');
    $file = File::load($image[0]);
    if (!empty($file)) {
      $file->setPermanent();
      $file->save();
      unset($file);
      $response_image = $image[0];
      unset($image);
    }

    $field = [
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'phone' => $form_state->getValue('phone'),
      'response' => $form_state->getValue('response'),
      'avatar' => $avatar,
      'response_image' => $response_image,
    ];

    $query = NULL;
    if ($this->id === 0) {
      $query = $database->insert($this->table)->fields(array_merge($field, ['data' => time()]));
    }
    else {
      $query = $database->upsert($this->table)->key('id')->fields(array_merge($field, ['id' => $this->id, 'data' => $this->data]));
    }
    try {
      $query->execute();
    }
    catch (\Exception $e) {
      echo $e;
    }
  }

}
