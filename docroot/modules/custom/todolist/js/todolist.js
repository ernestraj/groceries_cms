/**
 * @file
 * Behaviors for the To Do List.
 */

(function($, Drupal) {
  "use strict";
  Drupal.behaviors.todolist = {
    attach: function(context, settings) {
      let csrfToken;
      $.get(Drupal.url("session/token")).done(function(data) {
        csrfToken = data;
      });
      $(".todo-completed", context)
        .once("todolist")
        .click(function(e) {
          e.preventDefault();
          let id = $(this)[0].id;
          let item_id = id.split("-");
          let operation = "complete";
          if (
            $(this)
              .parents("div.todo-item")
              .hasClass("inactive")
          ) {
            operation = "start";
          }
          const json_data = { completed: item_id[1], operation };
          $.ajax({
            url: drupalSettings.path.baseUrl + "todolist/actions",
            data: JSON.stringify(json_data),
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-Token": csrfToken
            },
            type: "PUT",
            success: function(data) {
              if (data.operation == "completed") {
                $("#" + id)
                  .parents("div.todo-item")
                  .toggleClass("inactive");
              }
            }
          });
        });

      $(".todo-delete", context)
        .once("todolist")
        .click(function(e) {
          e.preventDefault();
          let id = $(this)[0].id;
          let item_id = id.split("-");
          const json_data = { delete: item_id[1] };
          $.ajax({
            url: drupalSettings.path.baseUrl + "todolist/actions",
            data: JSON.stringify(json_data),
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-Token": csrfToken
            },
            type: "DELETE",
            success: function(data) {
              if (data.operation == "completed") {
                $("#" + id)
                  .parents("div.todo-item")
                  .remove();
              }
            }
          });
        });

      $("#edit-actions-filter-all")
        .once("todolist")
        .click(function(e) {
          e.preventDefault();
          $(".todo-item").show();
          $(".todo-item.inactive").show();
        });

      $("#edit-actions-filter-completed")
        .once("todolist")
        .click(function(e) {
          e.preventDefault();
          $(".todo-item").hide();
          $(".todo-item.inactive").show();
        });

      $("#edit-actions-filter-uncompleted")
        .once("todolist")
        .click(function(e) {
          e.preventDefault();
          $(".todo-item").show();
          $(".todo-item.inactive").hide();
        });
    }
  };
})(jQuery, Drupal);
