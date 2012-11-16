<?php

/**
 * Dwoo
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 phpManufaktur by Ralf Hertsch
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!",
                $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

if (!defined('LEPTON_PATH'))
  require_once WB_PATH.'/modules/dwoo/wb2lepton.php';

function Dwoo_Plugin_show_wysiwyg_editor(Dwoo $dwoo, $name, $id, $content, $width='100%', $height='250px', $toolbar='default') {
  global $wysiwyg_editor_loaded;
  global $id_list;
  global $database;
  global $ckeditor;

  if (isset($_GET['page_id'])) {
    // special case: the $id_list is needed for multiple calls of the
    // WYSIWYG editor at page edit in the backend
    $id_list= array();
    $SQL = sprintf("SELECT `section_id` FROM `%ssections` WHERE `page_id`='%d' AND `module`='wysiwyg' ORDER BY `position`",
        TABLE_PREFIX, $_GET['page_id']);
    if (false === ($query = $database->query($SQL)))
      trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
    while (false !== ($wysiwyg_section = $query->fetchRow(MYSQL_ASSOC))) {
      $temp_id = (int) $wysiwyg_section['section_id'];
      $id_list[] = 'content'.$temp_id;
    }
  }

  if (!function_exists('show_wysiwyg_editor')) {
    if (!defined('WYSIWYG_EDITOR') || (WYSIWYG_EDITOR == 'none') ||
        !file_exists(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php')) {
      // no WYSIWYG editor available so use a textarea instead
      $content = sprintf('<textarea name="%s" id="%s" style="width: %s; height: %s;">%s</textarea>',
          $name, $id, $width, $height, $content);
      return $content;
    }
    else
      // load WYSIWYG editor
      require_once(LEPTON_PATH.'/modules/'.WYSIWYG_EDITOR.'/include.php');
  }

  $wysiwyg_admin_changed = false;

  if (class_exists('CKEditor_Plus')) {
    // force the width and height settings for CKE
    $ckeditor->force = true;
    // we change the menu only if it differ from 'default'
    if ($toolbar != 'default')
      $ckeditor->config['toolbar'] = $toolbar;
  }
  elseif (file_exists(LEPTON_PATH.'/modules/wysiwyg_admin/tool.php')) {
    // check the WYSIWYG admin settings
    $SQL = "SELECT `width`,`height`,`menu` FROM `".TABLE_PREFIX."mod_wysiwyg_admin` WHERE `editor` = '".WYSIWYG_EDITOR."'";
    $query = $database->query($SQL);
    if ($database->is_error())
      trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
    if ($query->numRows() == 1) {
      $old_values = $query->fetchRow(MYSQL_ASSOC);
      // does we need other values then spended from the WYSIWYG Admin?
      if (($width != $old_values['width']) || ($height != $old_values['height']) ||
          (($toolbar != 'default') && ($toolbar != $old_values['menu']))) {
        // we change the toolbar only if it differ from 'default'
        $toolbar = ($toolbar != 'default') ? $toolbar : $old_values['menu'];
        $SQL = "UPDATE `".TABLE_PREFIX."mod_wysiwyg_admin` SET `width`='$width', `height`='$height', ".
            "`menu`='$toolbar' WHERE `editor`='".WYSIWYG_EDITOR."'";
        if (!$database->query($SQL))
          trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
        $wysiwyg_admin_changed = true;
      }
    }
  }
  // get the complete WYSIWYG editor into $content
  ob_start();
  show_wysiwyg_editor($name, $id, $content, $width, $height);
  $content = ob_get_clean();
  $wysiwyg_editor_loaded = true;

  if (strpos($content, 'id="'.$id.'"') === false) {
    // fix: some editors like the CKE does not set the ID for the textarea!
    $content = str_replace('<textarea ', '<textarea id="'.$id.'" ', $content);
  }

  if ($wysiwyg_admin_changed) {
    // reset values for the WYSIWYG admin
    $SQL = "UPDATE `".TABLE_PREFIX."mod_wysiwyg_admin` SET `width`='{$old_values['width']}', ".
      "`height`='{$old_values['height']}', `menu`='{$old_values['menu']}' WHERE `editor`='".WYSIWYG_EDITOR."'";
    if (!$database->query($SQL))
      trigger_error(sprintf('[%s - %s] %s', __FUNCTION__, __LINE__, $database->get_error()), E_USER_ERROR);
  }
  return $content;
} // Dwoo_Plugin_show_wysiwyg_editor()
