<?php
/**
 * Layout file for the Admin view which displays the Events
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$user = Factory::getUser();
$userId = $user->get('id');
$saveOrder = ($listOrder == 'ordering' && strtolower($listDirn) == 'asc');
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_u3abooking&task=events.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'eventList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, false);
}
?>
<form action="index.php?option=com_u3abooking&view=events" method="post" id="adminForm" name="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span10">
                <?php echo JText::_('COM_U3ABOOKING_FILTERS'); ?>
                <?php
                    echo JLayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this)
                    );
                ?>
            </div>
        </div>
        <table class="table table-striped table-hover" id="eventList">
            <thead>
            <tr>
                <th width="1%">
                    <?php echo JHtml::_('searchtools.sort', '', 'ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
                <th width="1%"><?php echo JText::_('COM_U3ABOOKING_EVENTS_NUM'); ?></th>
                <th width="1%">
                    <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                <th width="20%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_TITLE', 'title', $listDirn, $listOrder); ?>
                </th>
				<th width="15%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_VENUE', 'venue', $listDirn, $listOrder); ?>
                </th>
				<th width="15%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_DATETIME', 'event_start', $listDirn, $listOrder); ?>
                </th>
				<th width="6%">
                    <?php echo JText::_('COM_U3ABOOKING_EVENTS_CAPACITY'); ?>
                </th>
				<th width="6%">
                    <?php echo JText::_('COM_U3ABOOKING_EVENTS_BOOKED'); ?>
                </th>
				<th width="6%">
                    <?php echo JText::_('JCATEGORY'); ?>
                </th>
                <th width="6%">
                    <?php echo JHtml::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
                </th>
                <th width="6%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_ORGANISER', 'organiser', $listDirn, $listOrder); ?>
                </th>
                <th width="6%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                    </th>
                <th width="5%">
                    <?php echo JText::_('COM_U3ABOOKING_EVENTS_STATE'); ?>
                </th>
                <th width="2%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_ID', 'id', $listDirn, $listOrder); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $row) :
                        $link = JRoute::_('index.php?option=com_u3abooking&task=event.edit&id=' . $row->id);
                    ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
                            <td><?php
                                $iconClass = '';
                                $canReorder  = $user->authorise('core.edit.state', 'com_u3abooking' . $row->id);
                                if (!$canReorder)
                                {
                                    $iconClass = ' inactive';
                                }
                                elseif (!$saveOrder)
                                {
                                    $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
                                }
                                ?>
                                <span class="sortable-handler<?php echo $iconClass ?>">
                                    <span class="icon-menu" aria-hidden="true"></span>
                                </span>
                                <?php if ($canReorder && $saveOrder) : ?>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="width-20 text-area-order" />
                                <?php endif; ?>
                            </td>
                            <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                            <td>
                                <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                            </td>
                            <td>
                                <?php if ($row->checked_out) : ?>
                                    <?php $canCheckin = $user->authorise('core.manage', 'com_checkin') || $row->checked_out == $userId; ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'events.', $canCheckin); ?>
                                <?php endif; ?>
                                <a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_U3ABOOKING_EDIT_EVENT'); ?>">
                                    <?php echo $row->title; ?>
                                </a>
								<div class="small">
                                	<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
                                </div>
                            </td>
                            <td align="center">
                                <?php echo $row->venue; ?>
                            </td>
							<td align="center">
                                <?php $sameDate = (substr($row->event_start, 0, 10) == substr($row->event_end, 0, 10)); ?>
								<?php echo substr($row->event_start, 0, 16) . ' - ' . ($sameDate ? substr($row->event_end, 11, 5) : substr($row->event_end, 0, 16)); ?>
                            </td>
							<td align="center">
                                <?php echo $row->capacity; ?>
                            </td>
							<td align="center">
                                <?php echo $row->tickets_taken; ?>
                            </td>
							<td align="center">
                                <?php echo $row->category_title; ?>
                            </td>
							<td align="center">
                                <?php echo $row->access_level; ?>
                            </td>
                            <td align="center">
                                <?php echo $row->organiser; ?>
                            </td>
                            <td align="center">
                                <?php echo substr($row->created, 0, 10); ?>
                            </td>
                            <td align="center">
                                <?php echo JHtml::_('jgrid.published', $row->published, $i, 'events.', true, 'cb'); ?>
                            </td>
                            <td align="center">
                                <?php echo $row->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php // load the modal for displaying the batch options
            //echo JHtml::_(
            //'bootstrap.renderModal',
            //'collapseModal',
            //array(
            //    'title' => JText::_('COM_HELLOWORLD_BATCH_OPTIONS'),
            //    'footer' => $this->loadTemplate('batch_footer')
            //),
            //$this->loadTemplate('batch_body')
        //); 
		?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>