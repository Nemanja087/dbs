<?php

/**
 * @file
 * Contains \Drupal\dbs\Form\DBSQuery.
 */

namespace Drupal\dbs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class DBSQuery extends FormBase {

    public function getFormId() {
        return 'db_search_form';
        // TODO: Implement getFormId() method.
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        // TODO: Implement buildForm() method.
        $form = array();

        $form['name'] = array(
            '#type' => 'markup',
            '#markup' => 'Query title',
        );

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => 'Submit'
        );

        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        // TODO: Implement submitForm() method.
        drupal_set_message('Form has been submitted successfully');
    }
}

