<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ToDoListForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'todolist';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    //kint($form_state->getStorage('todo_item'));
    $count =
      !empty($form_state->get('todo_item')) ? $form_state->get('todo_item') : 1;

    $form['#tree'] = TRUE;

    $form['todo_item'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => 'Please enter your todo item here'
      ],
      "#required" => TRUE
    ];
    $form['acions'] = [
      '#type' => 'acions'
    ];
    $form['acions']['add'] = [
      "#type" => "submit",
      "#value" => $this->t("Add To Do Item"),
      "#submit" => ['::addToDoValue'],
      '#ajax' => [
        "callback" => "::add_more_todo_items",
        "wrapper" => "edit-todo"
      ]
    ];

    // $form['todo_item_list']['actions'] = ['#type' => "actions"];
    // $form['todo_item_list']['actions']['add_more'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Add More'),
    //   '#submit' => ['::addMore'],
    //   '#ajax' => [
    //     'callback' => '::add_more_todo_items',
    //     'wrapper' => 'todo-item-list-wrapper',
    //   ],
    // ];

    // if ($count > 1) {
    //   $form['todo_item_list']['actions']['remove'] = [
    //     '#type' => 'submit',
    //     '#value' => $this->t('Remove'),
    //     '#submit' => ['::removeCallback'],
    //     '#ajax' => [
    //       'callback' => '::add_more_todo_items',
    //       'wrapper' => 'todo-item-list-wrapper',
    //     ],
    //   ];
    // }

    $form['#attached']['library'][] = 'todolist/todolist';
    $form_state->set('todo_item', $count);
    return $form;
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addMore(array &$form, FormStateInterface $form_state)
  {
    $todo = $form_state->get('todo_item');
    $add_button = $todo + 1;
    $form_state->set('todo_item', $add_button);

    $form_state->setRebuild();
  }

  public function add_more_todo_items(array &$form, FormStateInterface $form_state)
  {
    return $form['todo_item_list'];
  }

  public function removeCallback(array &$form, FormStateInterface $form_state)
  {
    $todo = $form_state->get('todo_item');
    if ($todo > 1) {
      $remove_button = $todo - 1;
      $form_state->set('todo_item', $remove_button);
    }
    // Since our buildForm() method relies on the value of 'num_names' to
    // generate 'name' form elements, we have to tell the form to rebuild. If we
    // don't do this, the form builder will not call buildForm().
    $form_state->setRebuild();
  }

  public function addToDoValue(array &$form, FormStateInterface $form_state)
  {
    print_r($form_state->getValues());
    die;

    $form_state->setRebuild();
  }


  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
  }
}
