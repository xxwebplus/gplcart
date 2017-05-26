<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($states) || $_filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
         <span class="caret"></span>
      </button>
      <?php $access_actions = false; ?>
      <?php if ($this->access('state_edit') || $this->access('state_delete')) { ?>
      <?php $access_actions = true; ?>
      <ul class="dropdown-menu">
        <?php if ($this->access('state_edit')) { ?>
        <li>
          <a data-action="status" data-action-value="1" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" data-action-confirm="<?php echo $this->text('Are you sure?'); ?>" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
          </a>
        </li>
        <?php } ?>
        <?php if ($this->access('state_delete')) { ?>
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
      <?php } ?>
    </div>
    <div class="btn-toolbar pull-right">
      <?php if ($this->access('state_add')) { ?>
      <a href="<?php echo $this->url("admin/settings/state/add/{$country['code']}"); ?>" class="btn btn-default add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>
  </div>
  <div class="panel-body table-responsive">
    <table class="table table-condensed country-states">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_state_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_code; ?>"><?php echo $this->text('Code'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th class="middle">
            <input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
            <input class="form-control" name="code" value="<?php echo $filter_code; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th class="middle">
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
          <th class="middle">
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
        <?php if($_filtering && empty($states)) { ?>
        <tr><td class="middle" colspan="6"><?php echo $this->text('No results'); ?></td></tr>
        <?php } ?>
        <?php foreach ($states as $state_id => $state) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $state_id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $state_id; ?></td>
          <td class="middle"><?php echo $this->e($state['name']); ?></td>
          <td class="middle"><?php echo $this->e($state['code']); ?></td>
          <td class="middle">
            <?php if ($state['status']) { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
              <ul class="list-inline">
                <?php if ($this->access('state_edit')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/settings/state/edit/{$country['code']}/$state_id"); ?>">
                    <?php echo $this->lower($this->text('Edit')); ?>
                  </a>
                </li>
                <?php } ?>
                <?php if ($this->access('city')) { ?>
                <li>
                  <a href="<?php echo $this->url("admin/settings/cities/{$country['code']}/$state_id"); ?>">
                    <?php echo $this->lower($this->text('Cities')); ?>
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
  <?php if(!empty($_pager)) { ?>
  <div class="panel-footer">
    <?php echo $_pager; ?>
  </div>
  <?php } ?>
</div>
<?php } else { ?>
<div class="row empty">
  <div class="col-md-12">
    <?php echo $this->text('This country has no states yet'); ?>
    <?php if ($this->access('state_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url("admin/settings/state/add/{$country['code']}"); ?>">
    <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>