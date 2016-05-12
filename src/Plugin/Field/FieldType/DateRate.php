<?php

namespace Drupal\daterate\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

/**
 * Plugin implementation of the 'daterate' field type.
 *
 * @FieldType (
 *   id = "daterate",
 *   label = @Translation("Date rate"),
 *   description = @Translation("Stores a date and rate."),
 *   default_widget = "daterate_default",
 *   default_formatter = "daterate"
 * )
 */
class DateRate extends DateTimeItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['rate'] = DataDefinition::create('float')
      ->setLabel(t('Rate'))
      ->setDescription(t('The Rate'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['rate'] = array(
      'type' => 'float',
      'unsigned' => TRUE,
    );
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value1 = parent::isEmpty();
    $value2 = $this->get('rate')->getValue();
    return $value1 && empty($value2);
  }

}
