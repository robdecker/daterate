<?php

namespace Drupal\daterate\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;

/**
 * Plugin implementation of the 'daterate' widget.
 *
 * @FieldWidget (
 *   id = "daterate_default",
 *   label = @Translation("Date rate"),
 *   field_types = {
 *     "daterate"
 *   }
 * )
 */
class DateRateDefaultWidget extends DateTimeDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    unset($element['#theme_wrappers']);
    $element['#attached']['library'][] = 'daterate/data-entry';
    $element['#type'] = 'fieldset';

    $element['#attributes']['class'][] = 'daterate-wrapper';
    if ($element['value']['#date_time_element'] == 'none') {
      $element['#attributes']['class'][] = 'daterate-wrapper--date-rate';
    }
    else {
      $element['#attributes']['class'][] = 'daterate-wrapper--date-time-rate';
    }

    // Override DateTime's settings.
    $element['value']['#title'] = t('Date');
    $element['value']['#description'] = '';
    $element['value']['#required'] = FALSE;
    $element['value']['#weight'] = 0;
    $element['value']['#field_parents'] = $element['#field_parents'];
    $element['value']['#prefix'] = '<div class="form-item">';
    $element['value']['#suffix'] = '</div>';

    $element['rate'] = array(
      '#type' => 'number',
      '#step' => '0.01',
      '#title' => t('Rate'),
      '#default_value' => isset($items[$delta]->rate) ? $items[$delta]->rate : 0.0,
      '#required' => FALSE,
      '#weight' => 1,
    );

    return $element;
  }
}
