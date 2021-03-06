<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Title'); ?>
    </label>
    <div class="col-md-4">
      <input maxlength="255" name="field[title]" class="form-control" value="<?php echo isset($field['title']) ? $this->e($field['title']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('title'); ?>
        <div class="text-muted">
          <?php echo $this->text('The title will be displayed to customers on product pages'); ?>
        </div>
      </div>
    </div>
  </div>
  <?php if (!empty($languages)) { ?>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <a data-toggle="collapse" href="#translations">
        <?php echo $this->text('Translations'); ?> <span class="caret"></span>
      </a>
    </div>
  </div>
  <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
    <?php foreach ($languages as $code => $language) { ?>
    <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['name'])); ?></label>
      <div class="col-md-4">
        <input maxlength="255" name="field[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($field['translation'][$code]['title'])) ? $this->e($field['translation'][$code]['title']) : ''; ?>">
        <div class="help-block">
          <?php echo $this->error("translation.$code.title"); ?>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if (empty($field['field_id'])) { ?>
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Type'); ?></label>
    <div class="col-md-4">
      <select name="field[type]" class="form-control">
        <?php foreach($types as $type => $type_name) { ?>
        <?php if (isset($field['type']) && $field['type'] == $type) { ?>
        <option value="<?php echo $this->e($type); ?>" selected>
        <?php echo $this->e($type_name); ?>
        </option>
        <?php } else { ?>
        <option value="<?php echo $this->e($type); ?>">
        <?php echo $this->e($type_name); ?>
        </option>
        <?php } ?>
        <?php } ?>
      </select>
      <div class="help-block">
        <?php echo $this->text('Atributes are facts about the products, options are interactive with the customer (Size, Color etc)'); ?>
      </div>
    </div>
  </div>
  <?php } ?>
  <div class="form-group">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Widget'); ?>
    </label>
    <div class="col-md-4">
      <select name="field[widget]" class="form-control">
        <?php foreach ($widget_types as $type => $name) { ?>
        <option value="<?php echo $type; ?>"<?php echo (isset($field['widget']) && $field['widget'] == $type) ? ' selected' : ''; ?>><?php echo $this->e($name); ?></option>
        <?php } ?>
      </select>
      <div class="help-block">
        <?php echo $this->text('Select how to display the field on product pages. This is for options only'); ?>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Weight'); ?></label>
    <div class="col-md-4">
      <input name="field[weight]" class="form-control" value="<?php echo isset($field['weight']) ? $this->e($field['weight']) : 0; ?>">
    <div class="help-block">
      <?php echo $this->error('weight'); ?>
      <div class="text-muted">
        <?php echo $this->text('Items are sorted in lists by the weight value. Lower value means higher position'); ?>
      </div>
    </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
      <?php if ($can_delete) { ?>
      <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
        <?php echo $this->text('Delete'); ?>
      </button>
      <?php } ?>
        <a class="cancel btn btn-default" href="<?php echo $this->url('admin/content/field'); ?>">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('field_edit') || $this->access('field_add')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>