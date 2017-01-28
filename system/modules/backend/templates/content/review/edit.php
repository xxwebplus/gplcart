<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-review" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo!empty($review['status']) ? ' active' : ''; ?>">
              <input name="review[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($product['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($review['status']) ? ' active' : ''; ?>">
              <input name="review[status]" type="radio" autocomplete="off" value="0"<?php echo empty($review['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
          <?php echo $this->text('Disabled reviews will not be available for frontend users and search engines'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('created', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Created'); ?></label>
        <div class="col-md-4">
          <input data-datepicker="true" data-datepicker-settings='{}' name="review[created]" class="form-control" value="<?php echo empty($review['created']) ? $this->date(null, false) : $this->date($review['created'], false); ?>">
          <div class="help-block">
            <?php echo $this->error('created'); ?>
            <div class="text-muted"><?php echo $this->text('A date when the review was created'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('product_id', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Product'); ?></label>
        <div class="col-md-6">
          <input name="review[product]" class="form-control" value="<?php echo isset($review['product']) ? $this->escape($review['product']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('product_id'); ?>
            <div class="text-muted"><?php echo $this->text('Required. Autocomplete field. Select a product that is related to this review'); ?></div>
          </div>
        </div>
      </div>
      <input type="hidden" name="review[product_id]" value="<?php echo isset($review['product_id']) ? $review['product_id'] : ''; ?>">
      <div class="form-group required<?php echo $this->error('email', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Email'); ?></label>
        <div class="col-md-6">
          <input name="review[email]" class="form-control" value="<?php echo isset($review['email']) ? $this->escape($review['email']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('email'); ?>
            <div class="text-muted"><?php echo $this->text('Required. Autocomplete field. Reviewer\'s E-mail'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo $this->error('text', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Text'); ?></label>
        <div class="col-md-6">
          <textarea name="review[text]" rows="4" class="form-control"><?php echo isset($review['text']) ? $this->escape($review['text']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->error('text'); ?>
            <div class="text-muted"><?php echo $this->text('Required. A text of the review. HTML not allowed'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($review['review_id']) && $this->access('review_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/content/review'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('review_edit') || $this->access('review_add')) { ?>
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>