<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

class ToDoListForm extends FormBase
{
  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @param \Drupal\Core\Database\Connection $connection
   *   The Connection object.
   */
  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'todolist';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#tree'] = TRUE;

    $form['todo_item_list'] = [
      '#type' => 'fieldset',
      '#prefix' => '<div id="edit-todo">',
      '#suffix' => '</div>'
    ];

    $form['todo_item_list']['todo_item'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'placeholder' => 'Please enter your todo item here'
      ]
    ];
    $form['todo_item_list']['acions'] = [
      '#type' => 'acions'
    ];

    $form['todo_item_list']['acions']['add'] = [
      "#type" => "submit",
      "#value" => $this->t("Add To Do Item"),
      "#submit" => ['::addToDoValue'],
      '#ajax' => [
        "callback" => "::add_more_todo_items",
        "wrapper" => "edit-todo",
        'event' => 'click'
      ]
    ];
    $items = $this->getAllToDoItemList();
    if ($items) {
      foreach ($items as $key => $item) {
        $markup = $this->getItemMarkup($item);
        $class = "todo-item";
        $class .= $item['completed'] == 0 ? '' : ' inactive';
        $form['todo_item_list']['item_list'][$item['id']]['item'] = [
          '#type' => 'markup',
          '#markup' => $markup,
          '#prefix' => '<div class= "' . $class . '" id = "todo-item-wrapper-' . $key . '">'
        ];
        $form['todo_item_list']['item_list'][$item['id']]['actions'] = [
          '#type' => 'actions',
        ];
        $form['todo_item_list']['item_list'][$item['id']]['actions']['complete'] = [
          '#type' => 'button',
          '#value' => $this->t('Complete'),
          '#attributes' => [
            'id' => 'complete-' . $item['id'],
            'class' => ['todo-completed']
          ]
        ];
        // $form['todo_item_list']['item_list'][$item['id']]['actions']['edit'] = [
        //   '#type' => 'submit',
        //   '#value' => $this->t('Edit'),
        //   '#name' => 'edit-' . $key,
        //   '#ajax' => [
        //     "callback" => "::edit_todo_item_form",
        //     "wrapper" => "todo-item-wrapper-" . $key,
        //     'event' => 'click'
        //   ]
        // ];
        $form['todo_item_list']['item_list'][$item['id']]['actions']['delete'] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete'),
          '#suffix' => '</div>',
          '#attributes' => [
            'id' => 'delete-' . $item['id'],
            'class' => ['todo-delete']
          ]
        ];
      }
      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['filter-all'] = [
        '#type' => 'submit',
        '#value' => $this->t('All')
      ];

      $form['actions']['filter-completed'] = [
        '#type' => 'submit',
        '#value' => $this->t('Filter Completed')
      ];

      $form['actions']['filter-uncompleted'] = [
        '#type' => 'submit',
        '#value' => $this->t('Filter Un Completed')
      ];
    }

    $form['#attached']['library'][] = 'todolist/todolist';

    return $form;
  }

  public function edit_todo_item_form(array &$form, FormStateInterface $form_state)
  {
    $element_key = $form_state->getTriggeringElement()['#parents'][2];
    $edit_form['todo-item-edit'] = [
      '#type' => 'fieldset',
    ];
    $edit_form['todo-item-edit']['edit-field'] = [
      '#type' => 'textfield',
      '#size' => '60',
      '#attributes' => [
        'id' => ['edit-output'],
      ],
    ];
    $edit_form['todo-item-edit']['edit'] = [
      '#type' => 'submit',
      "#value" => $this->t("Update"),
      "#submit" => ['::updateToDoValue'],
      '#ajax' => [
        "callback" => "::add_more_todo_items",
        "wrapper" => "edit-todo",
        'event' => 'click'
      ]
    ];

    $renderer = \Drupal::service('renderer');
    $renderedField = $renderer->render($edit_form);
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#todo-item-wrapper-' . $element_key, $renderedField));

    return $response;
  }

  public function getItemMarkup($item)
  {
    $markup = '';
    $markup .= '<div class="item"><span>';
    $markup .= $item['name'];
    $markup .= "</span></div>";

    return $markup;
  }

  public function getAllToDoItemList()
  {
    $result = $this->connection->select('todo', 'td')->fields('td')->execute()->fetchAll($fetch = \PDO::FETCH_ASSOC);
    if (!empty($result)) {
      return $result;
    } else {
      return FALSE;
    }
  }

  public function updateToDoValue(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRebuild(TRUE);
  }

  public function add_more_todo_items(array &$form, FormStateInterface $form_state)
  {
    $response = new AjaxResponse();
    return $form['todo_item_list'];
  }

  public function addToDoValue(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    if (!empty($values['todo_item_list']['todo_item'])) {
      $uid = \Drupal::currentUser()->id();
      $result = $this->connection->insert('todo')->fields(['uid' => $uid, 'name' => $values['todo_item_list']['todo_item']])->execute();
    }

    $form_state->setRebuild(TRUE);
  }


  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
  }
}
