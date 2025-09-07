<?php

namespace Drupal\pingvin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BlankForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'pingvin_forms.blank_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }
}
