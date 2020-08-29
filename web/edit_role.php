<?php
namespace MRBS;

use MRBS\Form\Element;
use MRBS\Form\ElementFieldset;
use MRBS\Form\ElementInputRadio;
use MRBS\Form\ElementInputSubmit;
use MRBS\Form\ElementP;
use MRBS\Form\FieldDiv;
use MRBS\Form\FieldInputRadioGroup;
use MRBS\Form\FieldInputText;
use MRBS\Form\FieldSelect;
use MRBS\Form\Form;

require "defaultincludes.inc";


function generate_add_role_form($error=null, $name=null)
{
  $form = new Form();
  $form->addHiddenInput('action', 'add')
       ->setAttributes(array('action' => multisite('edit_role_handler.php'),
                             'class'  => 'standard',
                             'method' => 'post'));

  // Name field
  $fieldset = new ElementFieldset();

  if (isset($error))
  {
    $field = new FieldDiv();
    $p = new ElementP();
    $p->setText(get_vocab($error, $name))
      ->setAttribute('class', 'error');
    $field->addControlElement($p);
    $fieldset->addElement($field);
  }

  $field = new FieldInputText();
  // Set a pattern as well as required to prevent a string of whitespace
  $field->setLabel(get_vocab('role_name'))
        ->setControlAttributes(array('name'     => 'name',
                                     'required' => true,
                                     'pattern'  => REGEX_TEXT_POS));
  if (null !== ($maxlength = maxlength('roles.name')))
  {
    $field->setControlAttribute('maxlength', $maxlength);
  }
  $fieldset->addElement($field);
  $form->addElement($fieldset);

  // Submit button
  $fieldset = new ElementFieldset();
  $field = new FieldDiv();
  $element = new ElementInputSubmit();
  $element->setAttribute('value', get_vocab('add_role'));
  $field->addControl($element);
  $fieldset->addElement($field);
  $form->addElement($fieldset);

  $form->render();
}


function generate_add_role_area_button(Role $role)
{
  $form = new Form();
  $form->addHiddenInputs(array('action' => 'add_role_area',
                               'role_id' => $role->id))
       ->setAttributes(array('action' => multisite(this_page()),
                             'class'  => 'standard',
                             'method' => 'post'));

  // Submit button
  $fieldset = new ElementFieldset();
  $field = new FieldDiv();
  $element = new ElementInputSubmit();
  $element->setAttribute('value', get_vocab('add_role_area'));
  $field->addControl($element);
  $fieldset->addElement($field);
  $form->addElement($fieldset);

  $form->render();
}


function generate_add_role_area_form(Role $role, $error, $area_id)
{
  $form = new Form();
  $form->addHiddenInputs(array('action' => 'add_role_area',
                               'role_id' => $role->id))
       ->setAttributes(array('action' => multisite('edit_role_handler.php'),
                             'class'  => 'standard',
                             'method' => 'post'));

  $fieldset = new ElementFieldset();

  if (isset($error))
  {
    $area = Area::getById($area_id);
    $field = new FieldDiv();
    $p = new ElementP();
    $p->setText(get_vocab($error, $area->area_name))
      ->setAttribute('class', 'error');
    $field->addControlElement($p);
    $fieldset->addElement($field);
  }

  // The area select
  $areas = new Areas();
  $field = new FieldSelect();
  $field->setLabel(get_vocab('area'))
        ->setControlAttributes(array('name' => 'area_id'))
        ->addSelectOptions($areas->getNames(true), null, true);
  $fieldset->addElement($field);

  // Permission
  $field = new FieldInputRadioGroup();
  $field->setLabel(get_vocab('permission'))
        ->addRadioOptions(AreaPermission::getPermissionOptions(),
                          'permission',
                          AreaPermission::$permission_default,
                          true);
  $fieldset->addElement($field);

  // State
  $field = new FieldInputRadioGroup();
  $field->setLabel(get_vocab('state'))
        ->addRadioOptions(AreaPermission::getStateOptions(),
                          'state',
                          AreaPermission::$state_default,
                          true);
  $fieldset->addElement($field);

  $form->addElement($fieldset);

  // Submit button
  $fieldset = new ElementFieldset();
  $field = new FieldDiv();
  $element = new ElementInputSubmit();
  $element->setAttribute('value', get_vocab('add_role_area'));
  $field->addControl($element);
  $fieldset->addElement($field);
  $form->addElement($fieldset);

  $form->render();
}


function generate_delete_button(Role $role)
{
  $form = new Form();
  $form->setAttributes(array('action' => multisite('edit_role_handler.php'),
                             'method' => 'post'));

  // Hidden inputs
  $form->addHiddenInputs(array(
    'action' => 'delete',
    'role_id' => $role->id
  ));

  // Submit button
  $button = new ElementInputSubmit();
  $message = get_vocab("confirm_del_role", $role->name);
  $button->setAttributes(array(
      'value' => get_vocab('delete'),
      'onclick' => "return confirm('" . escape_js($message) . "');"
    ));

  $form->addElement($button);
  $form->render();
}


function generate_roles_table()
{
  $roles = new Roles();

  echo "<table id=\"roles\">\n";
  echo "<tbody>\n";

  foreach ($roles as $role)
  {
    echo "<tr>";
    echo "<td>";
    generate_delete_button($role);
    echo "</td>";

    echo "<td>";
    $href = multisite(this_page() . '?role_id=' . $role->id);
    echo '<a href="' . htmlspecialchars($href). '">' . htmlspecialchars($role->name) . '</a>';
    echo "</td>";
    echo "</tr>\n";
  }

  echo "</tbody>\n";
  echo "</table>\n";
}


function generate_area_roles_table(Role $role)
{
  $permissions = new AreaPermissions();
  $permissions->getByRole($role);
  $permission_options = AreaPermission::getPermissionOptions();
  $state_options = AreaPermission::getStateOptions();

  $form = new Form();
  $form->setAttributes(array(
      'class' => 'standard'
    ));
  $fieldset = new ElementFieldset();

  $table = new Element('table');
  $thead = new Element('thead');
  $tr = new Element('tr');
  // Area name
  $th = new Element('th');
  $th->setAttribute('rowspan', 2)
     ->setText(get_vocab('area'));
  $tr->addElement($th);
  // Permission
  $th = new Element('th');
  $th->setAttribute('colspan', count($permission_options))
     ->setText(get_vocab('permission'));
  $tr->addElement($th);
  // State
  $th = new Element('th');
  $th->setAttribute('colspan', count($state_options))
    ->setText(get_vocab('state'));
  $tr->addElement($th);
  $thead->addElement($tr);

  $tr = new Element('tr');
  // Permission options
  foreach ($permission_options as $key => $value)
  {
    $th = new Element('th');
    $th->setText($value);
    $tr->addElement($th);
  }
  // State options
  foreach ($state_options as $key => $value)
  {
    $th = new Element('th');
    $th->setText($value);
    $tr->addElement($th);
  }
  $thead->addElement($tr);
  $table->addElement($thead);

  $tbody = new Element('tbody');


  foreach ($permissions as $permission)
  {
    $tr = new Element('tr');
    // Area name
    $th = new Element('th');
    $th->setText($permission->area_name);
    $tr->addElement($th);
    // Permission
    foreach ($permission_options as $option)
    {
      $td = new Element('td');
      $radio = new ElementInputRadio();
      $td->addElement($radio);
      $tr->addElement($td);
    }
    // State
    foreach ($permission_options as $option)
    {
      $td = new Element('td');
      $radio = new ElementInputRadio();
      $td->addElement($radio);
      $tr->addElement($td);
    }
    $tbody->addElement($tr);
  }

  $table->addElement($tbody);
  $fieldset->addElement($table);
  $form->addElement($fieldset);

  $form->render();;
}


// Check the user is authorised for this page
checkAuthorised(this_page());

$context = array(
    'view'      => $view,
    'view_all'  => $view_all,
    'year'      => $year,
    'month'     => $month,
    'day'       => $day,
    'area'      => isset($area) ? $area : null,
    'room'      => isset($room) ? $room : null
  );

$action = get_form_var('action', 'string');
$error = get_form_var('error', 'string');
$area_id = get_form_var('area_id', 'int');
$role_id = get_form_var('role_id', 'int');
$name = get_form_var('name', 'string');

print_header($context);

if (isset($role_id))
{
  $role = Role::getById($role_id);
  echo "<h2>" . htmlspecialchars(get_vocab('role_heading', $role->name)) . "</h2>";
  if (isset($action) && ($action == 'add_role_area'))
  {
    generate_add_role_area_form($role, $error, $area_id);
  }
  else
  {
    generate_add_role_area_button($role);
    generate_area_roles_table($role);
  }
}
else
{
  echo "<h2>" . htmlspecialchars(get_vocab('roles')) . "</h2>";
  generate_add_role_form($error, $name);
  generate_roles_table();
}

print_footer();
