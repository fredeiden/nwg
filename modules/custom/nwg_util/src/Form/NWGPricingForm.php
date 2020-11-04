<?php

namespace Drupal\nwg_util\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountInterface;

/**
 * Form to start batch
 */
class NWGPricingForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nwg_pricing_form';
  }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = null) {

      $form['pricing'] = array(
        '#type' => 'table',
        '#header' => array(
          'image' => 'Image',
          'title' => 'Title',
          'materials' => 'Materials',
          'price' => 'Price',
          'inventory' => 'Inventory',
        ),
      );

      $designs = \Drupal::entityQuery('node')
               ->condition('type', 'furniture', '=')
               ->condition('status', 1, '=')
               ->condition('field_artist', $user->get('field_artist')->target_id)
               ->sort('title', 'ASC')
               ->execute();
      $d_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($designs);

      foreach ($d_nodes as $d_key => $d_value) {

        $items = \Drupal::entityQuery('node')
               ->condition('type', 'item', '=')
               ->condition('status', 1, '=')
               ->condition('field_parent_design', $d_key)
               ->execute();
        $i_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($items);

        foreach ($i_nodes as $i_key => $i_value) {

          # image file
          $image_file = \Drupal\file\Entity\File::load($i_value->get('field_item_image')->target_id);

          # load materials entity reference as array
          $materials = array();
          foreach ($i_value->get('field_materials')->referencedEntities() as $term) {
            $materials[] = $term->getName();
          };

          # columns
          $form['pricing'][$i_key] = array(
            'image' => array(
              '#theme' => 'image_style',
              '#style_name' => 'square_tiny',
              '#uri' => $image_file->uri->value,
            ),
            'title' => array('#plain_text' => $i_value->getTitle()),
            'materials' => array('#plain_text' => implode(", ", $materials)),
            'price' => array(
                '#type' => 'number',
                '#default_value' => round($i_value->get('field_price')->value),
                '#field_prefix' => '$',
            ),
            'inventory' => array(
                '#type' => 'number',
                '#default_value' => $i_value->get('field_inventory')->value,
            ),
          );
        }
      }

      $form['submit_pricing'] = [
        '#type' => 'submit',
        '#value' => 'Save Changes',
      ];

      return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    foreach ($values['pricing'] as $nid => $value) {

      $changed = FALSE;
      $node = \Drupal\node\Entity\Node::load($nid);

      if ($value['price'] <> $node->get('field_price')->value) {
        $changed = TRUE;
        \Drupal::messenger()->addMessage(t("Changed price for %item", array('%item' => $node->getTitle())));
        $node->set('field_price', $value['price']);
      }

      if ($value['inventory'] <> $node->get('field_inventory')->value) {
        $changed = TRUE;
        \Drupal::messenger()->addMessage(t("Changed inventory for %item", array('%item' => $node->getTitle())));
        $node->set('field_inventory', $value['inventory']);
      }

      if ($changed) {
        $node->save();
      }
    }
  }

}
