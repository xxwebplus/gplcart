<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-checkout payment-methods panel-default">
  <div class="panel-heading clearfix">
    <?php echo $this->text('Payment'); ?>
    <noscript>
    <button title="<?php echo $this->text('Update'); ?>" class="btn btn-default btn-xs pull-right" name="update" value="1"><i class="fa fa-refresh"></i></button>
    </noscript>
  </div>
  <div class="panel-body">
    <?php if ($this->error('payment', true) && !is_array($this->error('payment'))) { ?>
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <?php echo $this->error('payment'); ?>
    </div>
    <?php } ?>
    <div class="form-group">
      <div class="col-md-12">
        <?php foreach ($payment_methods as $method_id => $method) { ?>
        <div class="radio">
          <label>
            <?php if (!empty($method['image'])) { ?>
            <img class="img-responsive" src="<?php echo $this->e($method['image']); ?>">
            <?php } ?>
            <input type="radio" name="order[payment]" value="<?php echo $this->e($method_id); ?>"<?php echo isset($order['payment']) && $order['payment'] == $method_id ? ' checked' : ''; ?>> <?php echo $this->e($method['title']); ?>
            <?php if (!empty($method['description'])) { ?>
            <div class="description small"><?php echo $this->filter($method['description']); ?></div>
            <?php } ?>
          </label>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>