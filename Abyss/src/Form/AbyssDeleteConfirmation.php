<?php

namespace Drupal\Abyss\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 *
 */
class AbyssDeleteConfirmation extends FormBase {
  /**
   * @var int
   * Contain id form in Database when form editing.
   */
  protected $id;

  /**
   * @var int
   * Contain information about
  */
  protected $check;

  /**
   *
   */
  public function getFormId() {
    // @todo Implement getFormId() method.
    return 'abyss_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $id = 0) {
    // @todo Implement buildForm() method.
    $this->id = $id;
    $this->check = 0;
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#ajax' => [
        'callback' => '::cancelForm',
        'event' => 'click',
      ],
    ];

    $form['cancel'] = [
      '#type' => 'button',
      '#value' => $this->t('Cancel'),
      '#ajax' => [
        'callback' => '::cancelForm',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   * Delete modal form when user push close button.
   */
  public function cancelForm(array &$form, FormStateInterface &$form_state): AjaxResponse {
    $response = new AjaxResponse();

    $response->addCommand(new RemoveCommand('[id*="abyss-delete-"]'));
    $response->addCommand(new RemoveCommand('[id*="drupal-dialog-abysspage-delete-"]'));
    $response->addCommand(new RemoveCommand('.ui-dialog'));

    return $response;
  }

  /**
   * {@inheritdoc}
   * Removes information about the comment and the image to it in tables Abyss,
   * and file_managed in Database and files.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Implement submitForm() method.
    $query = \Drupal::database()->select('Abyss', 'ab');
    $query->fields('ab', [
      'avatar',
      'response_image',
    ]);
    $query->condition('id', $this->id, '=');
    $elements = $query->execute()->fetch();

    $file = File::load($elements->avatar);
    if (!empty($file)) {
      $file->setTemporary();
      $file->save();
      $file->delete();
      unset($file);
    }

    $file = File::load($elements->response_image);
    if (!empty($file)) {
      $file->setTemporary();
      $file->save();
      $file->delete();
      unset($file);
    }

    $query = \Drupal::database()->delete('Abyss');
    $query->condition('id', $this->id, '=');
    $query->execute();

    rmdir('public://abyss/' . $this->id);

    $this->check = 1;
  }

}
