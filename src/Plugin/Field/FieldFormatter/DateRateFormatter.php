<?php

namespace Drupal\daterate\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;
use Drupal\Core\Form\FormStateInterface;

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
  public static function defaultSettings() {
    return array(
      'separator' => ': ',
      'component_order' => 'date_first',
      'symbol' => '$',
      'symbol_position' => 'before',
      'decimal_places' => 2,
      'decimal_separator' => '.',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['separator'] = array(
      '#type' => 'textfield',
      '#title' => t('Character(s) to use in between the date/time and the rate'),
      '#default_value' => $this->getSetting('separator'),
    );

    $elements['component_order'] = array(
      '#type' => 'select',
      '#title' => t('The rendering order of the date/time and the rate'),
      '#options' => $this->getComponentOrderOptions(),
      '#default_value' => $this->getSetting('component_order'),
    );

    $elements['symbol'] = array(
      '#type' => 'textfield',
      '#title' => t('Character(s) to use for the rate symbol'),
      '#default_value' => $this->getSetting('symbol'),
    );

    $elements['symbol_position'] = array(
      '#type' => 'select',
      '#title' => t('The rendering order of the symbol and the rate'),
      '#options' => $this->getSymbolPositionOptions(),
      '#default_value' => $this->getSetting('symbol_position'),
    );

    $elements['decimal_places'] = array(
      '#type' => 'number',
      '#title' => t('Number of decimal places to use'),
      '#default_value' => $this->getSetting('decimal_places'),
      '#step' => '1.0',
    );

    $elements['decimal_separator'] = array(
      '#type' => 'textfield',
      '#title' => t('Character(s) to use for the decimal separator'),
      '#default_value' => $this->getSetting('decimal_separator'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $settings = $this->getSettings();
    $options = $this->getComponentOrderOptions();
    $positions = $this->getSymbolPositionOptions();

    if (!empty($settings['separator'])) {
      $summary[] = t('Separator using character(s): @separator', array('@separator' => $settings['separator']));
    }
    else {
      $summary[] = t('No separator specified');
    }

    $summary[] = t('Order: @order', array('@order' => $options[$this->getSetting('component_order')]));

    if (!empty($settings['symbol'])) {
      $summary[] = t('Character(s) for symbol: @symbol', array('@symbol' => $settings['symbol']));
    }
    else {
      $summary[] = t('No symbol specified');
    }

    $summary[] = t('Decimal places: @decimal_places', array('@decimal_places' => number_format($settings['decimal_places'])));

    if (!empty($settings['decimal_separator'])) {
      $summary[] = t('Character(s) for decimal separator: @decimal_separator', array('@decimal_separator' => $settings['decimal_separator']));
    }
    else {
      $summary[] = t('No decimal separator specified');
    }

    $summary[] = t('Symbol position: @position', array('@position' => $positions[$this->getSetting('symbol_position')]));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = array();

    // Display the date using theme datetime.
    $date_element = array(
      '#cache' => [
        'contexts' => [
          'timezone',
        ],
      ],
      '#theme' => 'time',
      '#text' => '',
      '#html' => FALSE,
      '#attributes' => array(
        'datetime' => '',
        'class' => array('date'),
      ),
    );

    $rate_element = array(
      '#type' => 'markup',
      '#prefix' => '<span class="rate">',
      '#suffix' => '</span>',
      '#markup' => '',
    );

    $separator_element = array(
      '#type' => 'markup',
      '#prefix' => '<span class="labeled-telephone__separator">',
      '#suffix' => '</span>',
      '#markup' => $this->getSetting('separator'),
    );

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

      $date_element['#text'] = $output;
      $date_element['#attributes']['datetime'] = $iso_date;

      $rate_element['#markup'] = $this->formatRate($item->rate);

      if (!empty($item->_attributes)) {
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }

      switch ($this->getSetting('component_order')) {
        case 'date_first':
          $elements[$delta][0] = $date_element;
          $elements[$delta][1] = $separator_element;
          $elements[$delta][2] = $rate_element;
          break;

        case 'rate_first':
        default:
          $elements[$delta][0] = $rate_element;
          $elements[$delta][1] = $separator_element;
          $elements[$delta][2] = $date_element;
          break;
      }
    }

    return $elements;
  }

  /**
   * Gets all possible sub-field ordering options.
   *
   * @return array
   *   The array of options.
   */
  protected function getComponentOrderOptions() {
    $options = array(
      'date_first' => 'Date first, rate second',
      'rate_first' => 'Rate first, date second',
    );

    return $options;
  }

  /**
   * Gets all possible symbol ordering options.
   *
   * @return array
   *   The array of options.
   */
  protected function getSymbolPositionOptions() {
    $options = array(
      'before' => 'Symbol before rate',
      'after' => 'Symbol after rate',
    );

    return $options;
  }

  /**
   * Format the rate for output.
   *
   * @return string
   *   The formatted rate.
   */
  protected function formatRate($rate) {
    $symbol = $this->getSetting('symbol');
    $symbol_position = $this->getSetting('symbol_position');
    $decimal_places = $this->getSetting('decimal_places');
    $decimal_separator = $this->getSetting('decimal_separator');

    $output = number_format($rate, $decimal_places, $decimal_separator, '');
    switch ($symbol_position) {
      case 'before':
        $output = $symbol . $output;
        break;

      case 'after':
      default:
        $output = $output . $symbol;
        break;
    }

    return $output;
  }

}
