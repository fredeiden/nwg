<?php

namespace Drupal\nwg_util\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountInterface;

/**
 * Form to start batch
 */
class NWGPortfolioForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nwg_portfolio_form';
  }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = null) {

      $form['portfolio'] = array(
        '#type' => 'table',
        '#header' => array(
#          'image' => 'Image',
#          'title' => 'Title',
#          'materials' => 'Materials',
#          'hide' => 'Hide',
#          'weight' => 'Weight',
        ),
        '#tabledrag' => array(
          array(
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'mytable-order-weight',
          ),
        ),
      );

      $designs = \Drupal::entityQuery('node')
            ->condition('type', 'furniture', '=')
            ->condition('status', 1, '=')
               ->condition('field_artist', $user->get('field_artist')->target_id)
            ->execute();
      $d_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($designs);

      foreach ($d_nodes as $d_key => $d_value) {

        $items = \Drupal::entityQuery('node')
               ->condition('type', 'item', '=')
               ->condition('status', 1, '=')
#               ->condition('field_portfolio', FALSE)
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
          $form['portfolio'][$i_key] = array(
            '#attributes' => array('class' => array('draggable')),
            '#weight' => $i_value->get('field_portfolio_weight')->value,
            'image' => array(
              '#theme' => 'image_style',
              '#style_name' => 'square_tiny',
              '#uri' => $image_file->uri->value,
            ),
#            'title' => array('#plain_text' => $i_value->getTitle()),
#            'materials' => array('#plain_text' => implode(", ", $materials)),
            'hide' => array(
              '#type' => 'checkbox',
              '#title' => 'Hide',
              '#default_value' => $i_value->get('field_portfolio')->value,
            ),
            'weight' => array(
              '#type' => 'weight',
              '#title' => 'Weight',
              '#title_display' => 'invisible',
              '#default_value' => $i_value->get('field_portfolio_weight')->value,
              '#delta' => 50,
              '#attributes' => array('class' => array('mytable-order-weight')),
            ),
          );
        }
      }

      $form['submit_portfolio'] = [
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

    foreach ($values['portfolio'] as $nid => $value) {

      $changed = FALSE;
      $node = \Drupal\node\Entity\Node::load($nid);

      if ($value['hide'] <> $node->get('field_portfolio')->value) {
        $node->set('field_portfolio', $value['hide']);
        $changed = TRUE;
      }
      if ($value['weight'] <> $node->get('field_portfolio_weight')->value) {
        $node->set('field_portfolio_weight', $value['weight']);
        $changed = TRUE;
      }

      if ($changed) {
        $node->save();
      }
    }
  }

}
