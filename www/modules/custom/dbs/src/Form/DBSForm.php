<?php

/**
 * @file
 * Contains \Drupal\dbs\Form\DBSForm.
 */

namespace Drupal\dbs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal;

class DBSForm extends FormBase {

    public function getFormId() {
        return 'db_search_form';
    }

    /*
     * Initial form build.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = [];

        $form['name'] = [
            '#type' => 'markup',
            '#markup' => 'Form title',
        ];

        $form['options'] = [
            '#type' => 'select',
            '#title' => t('Content type:'),
            '#options' => $this->_get_all_content_types(),
        ];

        $form['users'] = [
            '#type' => 'select',
            '#title' => t('User:'),
            '#options' => $this->_get_all_users(),
        ];

        $form['status'] = [
            '#type' => 'select',
            '#title' => t('Publishing status:'),
            '#options' => [
                -1 => t('All'),
                0 => t('Unpublished'),
                1 => t('Published'),
            ],
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => 'Submit',
        ];

        $header = [
            'id' => t('Id'),
            'title' => t('Title'),
            'status' => t('Published'),
            'created' => t('Created'),
            'created_by' => t('Created By'),
            'edit' => t('Action'),
        ];

        $values = $form_state->getValues();

        $results = $this->_get_results($values['options'], $values['status'], $values['users']);

        // Build table on submit.
        // @todo hide table if post is not triggered.
        $form['results'] = [
            '#type' => 'tableselect',
            '#header' => $header,
            '#options' => $this->_parse_results($results),
            '#empty' => t('No Data'),
        ];

        return $form;
    }

    /*
     * Form submit.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        if(!empty($form_state->getValues()) && $form['#form_id'] == $this->getFormId()) {
            $form_state->setRebuild();
        }
    }

    /*
     * Query select all content type's.
     */
    function _get_all_content_types() {

        $SQL = "SELECT n.type FROM node n GROUP BY n.type";

        $result = db_query($SQL)->fetchAll();

        $type = [];

        foreach ($result as $types) {
            $type[$types->type] = $types->type;
        }

        return $type;
    }

    /*
     * Query select all content type's WHERE selected option.
     */
    function _get_results($type = '', $status = -1, $uid = NULL) {

        $query = \Drupal::database()->select('node_field_data', 'nfd');
        $query->fields('nfd', ['nid', 'uid', 'type', 'title', 'status', 'created']);

        //content type check.
        if(!empty($type)) {
            $query->condition('nfd.type', $type);
        }
        //status check if not all.
        if($status != -1) {
            $query->condition('nfd.status', (int)$status);
        }
        //Sort by user.
        if(!empty($uid)) {
            $query->condition('nfd.uid', $uid);
        }

        return $query->execute();
    }

    /*
     * Query get user name.
     */
    function _get_all_users() {
        $SQL = "SELECT nfd.uid, ufd.name FROM node_field_data nfd JOIN users_field_data ufd WHERE nfd.uid = ufd.uid";

        $user = [];

        $user = db_query($SQL)->fetchAllKeyed();

        return $user;
    }

    /*
     * Parsing username by user id.
     */
    function _parse_username($uid = NULL) {

        $result = '';

        if(!empty($uid)) {
            $SQL = "SELECT ufd.uid, ufd.name FROM users_field_data ufd WHERE ufd.uid = {$uid}";

            $result = db_query($SQL)->fetchAllKeyed((int)'ufd.uid');
        }

        return $result;
    }

    /*
     * Parse results to match table header.
     */
    function _parse_results($results = []) {

        $result = [];

        foreach ($results as $res) {
            $result[$res->nid] = [
                'id' => $res->nid,
                'title' => Drupal::l($res->title, Url::fromUri('internal:/node/' . $res->nid)),
                'status' => $res->status == 1 ? 'yes' : 'no',
                'created' => date('m/d/Y | H:i:s', $res->created),
                'created_by' => $this->_parse_username((int)$res->uid),
                'edit' => Drupal::l('Edit', Url::fromUri('internal:/node/' . $res->nid . '/edit')),
            ];
        }
        return $result;
    }
}
