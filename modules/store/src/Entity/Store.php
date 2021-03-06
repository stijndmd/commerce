<?php

/**
 * @file
 * Contains \Drupal\commerce\Entity\Store.
 */

namespace Drupal\commerce_store\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\commerce_store\StoreInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Commerce Store entity type.
 *
 * @ContentEntityType(
 *   id = "commerce_store",
 *   label = @Translation("Store"),
 *   bundle_label = @Translation("Store type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_store\StoreListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\commerce_store\Form\StoreForm",
 *       "edit" = "Drupal\commerce_store\Form\StoreForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "commerce_store",
 *   data_table = "commerce_store_field_data",
 *   admin_permission = "administer stores",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "store_id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/store/{commerce_store}",
 *     "edit-form" = "/admin/commerce/config/store/{commerce_store}/edit",
 *     "delete-form" = "/admin/commerce/config/store/{commerce_store}/delete",
 *     "collection" = "/admin/commerce/config/store",
 *   },
 *   bundle_entity_type = "commerce_store_type",
 *   field_ui_base_route = "entity.commerce_store_type.edit_form",
 * )
 */
class Store extends ContentEntityBase implements StoreInterface {

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no owner has been set explicitly, make the current user the owner.
    if (!$this->getOwner()) {
      $this->setOwnerId($this->getCurrentUserId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('store_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmail() {
    return $this->get('mail')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEmail($mail) {
    $this->set('mail', $mail);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultCurrency() {
    return $this->get('default_currency')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultCurrency($currency_code) {
    $this->set('default_currency', $currency_code);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {
    $fields['store_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Store ID'))
      ->setDescription(t('The ID of the store.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the store.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Store owner'))
      ->setDescription(t('The user that owns this store.'))
      ->setDefaultValueCallback('Drupal\commerce_store\Entity\Store::getCurrentUserId')
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 50,
      ));

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code of store.'))
      ->setDisplayOptions('view', array(
        'type' => 'hidden',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 2,
      ));

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Store name'))
      ->setDescription(t('The name of the store.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 255,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -10,
      ));

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of the store.'))
      ->setRequired(TRUE);

    $fields['mail'] = BaseFieldDefinition::create('email')
      ->setLabel(t('E-mail address'))
      ->setDescription(t('A valid e-mail address. Store e-mail notifications will be sent to and from this address.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'email_default',
        'weight' => 0,
      ));

    $fields['default_currency'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Default currency'))
      ->setDescription(t('The default currency of this store.'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'commerce_currency')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 10,
        'settings' => array(),
      ));

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return array(\Drupal::currentUser()->id());
  }

}
