<?php

namespace Drupal\daterate\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;

/**
 * Plugin implementation of the 'daterate' formatter.
 *
 * @FieldFormatter (
 *   id = "daterate",
 *   label = @Translation("DateRate"),
 *   field_types = {
 *     "daterate"
 *   }
 * )
 */
class DateRateFormatter extends DateTimeDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = array();

    foreach ($items as $delta => $item) {
      $output = '';
      $iso_date = '';

      if ($item->date) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
        $date = $item->date;

        if ($this->getFieldSetting('datetime_type') == 'date') {
          // A date without time will pick up the current time, use the default.
          datetime_date_default_time($date);
        }

        // Create the ISO date in Universal Time.
        $iso_date = $date->format("Y-m-d\TH:i:s") . 'Z';

        $this->setTimeZone($date);

        $output = $this->formatDate($date);
      }

      // Display the date using theme datetime.
      $elements[$delta][0] = array(
        '#cache' => [
          'contexts' => [
            'timezone',
          ],
        ],
        '#theme' => 'time',
        '#text' => $output,
        '#html' => FALSE,
        '#attributes' => array(
          'datetime' => $iso_date,
          'class' => array('date'),
        ),
      );
      if (!empty($item->_attributes)) {
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }

      $elements[$delta][1] = array(
        '#type' => 'markup',
        '#prefix' => '<span class="separator">',
        '#suffix' => '</span>',
        '#markup' => ' - ',
      );

      $elements[$delta][2] = array(
        '#type' => 'markup',
        '#prefix' => '<span class="rate">',
        '#suffix' => '</span>',
        '#markup' => '$' . number_format($item->rate, 2, '.', ''),
      );
    }

    return $elements;
  }

}
