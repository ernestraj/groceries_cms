<?php

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    //kint($form_state->getStorage('todo_item'));
    $count =
      !empty($form_state->get('todo_item')) ? $form_state->get('todo_item') : 1;

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
    $items = $this->getAllToDoItemList();
    if ($items) {
      foreach ($items as $key => $item) {
        $markup = $this->getItemMarkup($item);
        $class = $item['completed'] == 0 ? 'active' : 'inactive';
        $form['todo_item_list']['item_list'][$key]['item'] = [
          '#type' => 'markup',
          '#markup' => $markup,
          '#prefix' => '<div class= "' . $class . '">'
        ];
        $form['todo_item_list']['item_list'][$key]['actions'] = [
          '#type' => 'actions',
        ];
        $form['todo_item_list']['item_list'][$key]['actions']['complete'] = [
          '#type' => 'button',
          '#value' => $this->t('Complete'),
          '#attributes' => [
            'id' => 'complete-' . $item['id'],
            'class' => ['todo-completed']
          ]
        ];
        $form['todo_item_list']['item_list'][$key]['actions']['edit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Edit')
        ];
        $form['todo_item_list']['item_list'][$key]['actions']['delete'] = [
          '#type' => 'submit',
          '#value' => $this->t('Delete'),
          '#suffix' => '</div>'
        ];
      }
    }

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

    $form['#attached']['library'][] = 'todolist/todolist';
    return $form;
  }

  public function completeToDoValue(array &$form, FormStateInterface $form_state)
  {
    print_r($form_state->getValues());
    die;
  }

  public function getItemMarkup($item)
  {
    $markup .= '<div class="item">';
    $markup .= $item['name'];
    $markup .= "</div>";

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

  public function add_more_todo_items(array &$form, FormStateInterface $form_state)
  {
    return $form['todo_item_list'];
  }

  public function addToDoValue(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    if (!empty($values['todo_item_list']['todo_item'])) {
      $uid = \Drupal::currentUser()->id();
      $result = $this->connection->insert('todo')->fields(['uid' => $uid, 'name' => $values['todo_item_list']['todo_item']])->execute();
    }

    $form_state->setRebuild();
  }


  public function validateForm(array &$form, FormStateInterface $form_state)
  {
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
  }
}
