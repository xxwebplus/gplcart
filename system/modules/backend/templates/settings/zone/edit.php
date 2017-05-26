<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-zone" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label">
            <?php echo $this->text('Status'); ?>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($zone['status']) ? '' : ' active'; ?>">
              <input name="zone[status]" type="radio" autocomplete="off" value="1"<?php echo empty($zone['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($zone['status']) ? ' active' : ''; ?>">
              <input name="zone[status]" type="radio" autocomplete="off" value="0"<?php echo empty($zone['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="text-muted">
            <?php echo $this->text('Disabled zones will not be available for administrators and customers'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input name="zone[title]" maxlength="255" class="form-control" value="<?php echo isset($zone['title']) ? $this->e($zone['title']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('title'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        </div>
        <div class="col-md-4">
      <div class="btn-toolbar">
        <a class="btn btn-default" href="<?php echo $this->url('admin/settings/zone'); ?>">
          <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('zone_edit') || $this->access('zone_add')) { ?>
        <button class="btn btn-default" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
        </div>
      </div>
    </div>
  </div>
</form>