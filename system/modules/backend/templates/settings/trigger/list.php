<?php if (!empty($triggers) || $filtering) { ?>
<form method="post" id="triggers" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-heading clearfix">
      <?php if ($this->access('trigger_edit') || $this->access('trigger_delete')) { ?>
      <div class="btn-group pull-left">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo $this->text('With selected'); ?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
          <?php if ($this->access('trigger_edit')) { ?>
          <li>
            <a data-action="status" data-action-value="1" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
            </a>
          </li>
          <li>
            <a data-action="status" data-action-value="0" href="#">
              <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
            </a>
          </li>
          <?php } ?>
          <?php if ($this->access('trigger_delete')) { ?>
          <li>
            <a data-action="delete" href="#">
              <?php echo $this->text('Delete'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
      <?php } ?>
      <?php if ($this->access('trigger_add')) { ?>
      <div class="btn-toolbar pull-right">
        <a class="btn btn-default" href="<?php echo $this->url('admin/settings/trigger/add'); ?>">
          <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
        </a>
      </div>
      <?php } ?>
    </div>
    <div class="panel-body table-responsive">
      <table class="table table-striped triggers">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th>
              <a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_store_id; ?>"><?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th>
              <a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a>
            </th>
            <th></th>
          </tr>
          <tr class="filters active">
            <th></th>
            <th>
              <input class="form-control" name="name" maxlength="255" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
            </th>
            <th>
              <select class="form-control" name="store_id">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <?php foreach ($stores as $store_id => $store) { ?>
                <option value="<?php echo $store_id; ?>"<?php echo ($filter_store_id == $store_id) ? ' selected' : ''; ?>>
                <?php echo $this->escape($store); ?>
                </option>
                <?php } ?>
              </select>
            </th>
            <th>
              <select class="form-control" name="status">
                <option value="any"><?php echo $this->text('Any'); ?></option>
                <option value="1"<?php echo ($filter_status === '1') ? ' selected' : ''; ?>>
                <?php echo $this->text('Enabled'); ?>
                </option>
                <option value="0"<?php echo ($filter_status === '0') ? ' selected' : ''; ?>>
                <?php echo $this->text('Disabled'); ?>
                </option>
              </select>
            </th>
            <th>
              <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
                <i class="fa fa-refresh"></i>
              </button>
              <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
                <i class="fa fa-search"></i>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if ($filtering && empty($triggers)) { ?>
          <tr>
            <td colspan="5">
              <?php echo $this->text('No results'); ?>
              <a class="clear-filter" href="#"><?php echo $this->text('Reset'); ?></a>
            </td>
          </tr>
          <?php } ?>
          <?php foreach ($triggers as $id => $trigger) { ?>
          <tr>
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>">
            </td>
            <td class="middle"><?php echo $this->escape($trigger['name']); ?></td>
            <td class="middle">
              <?php if (isset($stores[$trigger['store_id']])) { ?>
              <?php echo $this->escape($stores[$trigger['store_id']]); ?>
              <?php } else { ?>
              <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if (empty($trigger['status'])) { ?>
              <i class="fa fa-square-o"></i>
              <?php } else { ?>
              <i class="fa fa-check-square-o"></i>
              <?php } ?>
            </td>
            <td class="middle">
                <ul class="list-inline">
                  <?php if ($this->access('trigger_edit')) { ?>
                  <li>
                    <a href="<?php echo $this->url("admin/settings/trigger/edit/$id"); ?>">
                      <?php echo strtolower($this->text('Edit')); ?>
                    </a>
                  </li>
                  <?php } ?>
                </ul>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if (!empty($pager)) { ?>
    <div class="panel-footer"><?php echo $pager; ?></div>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no triggers'); ?>
    <?php if ($this->access('trigger_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/settings/trigger/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>