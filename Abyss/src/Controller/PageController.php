<?php

namespace Drupal\Abyss\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Simple page controller for drupal.
 */
class PageController extends ControllerBase {

  /**
   * @return string[]
   *   {@inheritdoc}
   *   Performs rendering to display the form, saved information,
   *   and pager to navigate pages with broken information.
   */
  public function page() {
    // Creating a pager.
    $total = \Drupal::database()->select('Abyss', 'ab')->countQuery()->execute()->fetchField();
    $num_per_page = 5;
    $page = pager_default_initialize($total, $num_per_page);
    $offset = $num_per_page * $page;

    // Checking user rights.
    $user = \Drupal::currentUser();
    $user_admin = $user->hasPermission('administer comments');

    // Sample comments from the database.
    $query = \Drupal::database()->select('Abyss', 'ab');
    $query->fields('ab', [
      'id',
      'name',
      'email',
      'phone',
      'response',
      'avatar',
      'response_image',
      'data',
    ]);
    $query->orderBy('id', 'DESC')->range($offset, $num_per_page)->extend('Drupal\\Core\\Database\\Query\\PagerSelectExtender');
    $elements = $query->execute()->fetchAll();
    unset($query);

    /**
     * Data verification, namely: the presence of an avatar image,
     * image to respond, date formatting.
     * If there is no avatar image, it is assigned a default image.
    */
    global $base_path;
    foreach ($elements as &$element) {
      $element = json_decode(json_encode($element), TRUE);
      // Avatar image check.
      if (is_null($element['avatar'])) {
        $element['avatar'] = $base_path . drupal_get_path('module', 'Abyss') . '/images/256.svg';
      }
      else {
        $query = \Drupal::database()->select('file_managed', 'fm');
        $query->addField('fm', 'uri');
        $query->condition('fm.fid', $element['avatar'], '=');
        $element['avatar'] = $query->execute()->fetchField();
        unset($query);
        $element['avatar'] = file_url_transform_relative(file_create_url($element['avatar']));
      }

      // Response image check.
      if (!is_null($element['response_image'])) {
        $query = \Drupal::database()->select('file_managed', 'fm');
        $query->addField('fm', 'uri');
        $query->condition('fm.fid', $element['response_image'], '=');
        $element['response_image'] = $query->execute()->fetchField();
        unset($query);
        $element['response_image'] = file_url_transform_relative(file_create_url($element['response_image']));

        /**
         * or
         * $file = File::load($element['response_image']);
         * 1. file_url_transform_relative(file_create_url($file->getFileUri()));
         * or
         * 2. $file->createFileUrl();
         * unset($file);
         * or
         * 3.
         * $file = File::load($element['response_image']);
         * $element['response_image'] = Url::fromUri(file_create_url($file->getFileUri()))->ToString();
         */
      }

      // Date formatting.
      $element['data'] = date('d/m/Y H:i:s', $element['data']);
    }

    // Call and form rendering for connection.
    $form_class = '\Drupal\Abyss\Form\AbyssEditModalForm';
    $form = \Drupal::formBuilder()->getForm($form_class);
    $renderer = \Drupal::service('renderer')->render($form);

    // Data generation for return for Abyss Theme hook.
    $build['comment'] = [
      '#theme' => 'description',
      '#fields' => $elements,
      '#form' => $renderer,
      '#user' => ['admin' => $user_admin],
    ];

    $build['pager'] = [
      '#theme' => 'pager',
      '#type' => 'pager',
    ];

    return $build;
  }

}
