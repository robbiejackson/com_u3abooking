<?php
/**
 * View for displaying an event and allowing booking against it
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class U3ABookingViewBooking extends HtmlView
{
	/**
	 * Display the event and form for booking
	 */
	
	function display($tpl = null)
	{
		$eventModel = BaseDatabaseModel::getInstance('Event', 'U3ABookingModel');
		$bookingModel = $this->getModel();
		
		$user = Factory::getUser();
		$app = Factory::getApplication();
		
		// find the booking (if it's an edit)
		// and find the event
		$bookingId = $app->input->get('id', '', 'string');
		if (!empty($bookingId))
		{	// edit booking
			$this->booking = $bookingModel->getItem($bookingId);
			$this->event = $eventModel->getItem($this->booking->event_id);
		}
		else
		{	// add booking
			$this->event = $eventModel->getItem();
		}

		// handle if event not found (eg event date is passed or event not published)
		if (!$this->event)
		{
			$app->enqueueMessage(JText::_('COM_U3ABOOKING_NO_EVENT'), 'warning');
		}
		
		// Take action based on whether the user has access to see the record or not
		$loggedIn = $user->get('guest') != 1;
		if ($this->event && !$this->event->canAccess)
		{
			if ($loggedIn)
			{	// they're never going to get access
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
				$app->setHeader('status', 403, true);
				return;
			}
			else
			{	// force logon - redirect to com_users, with "return" set to return to this page after logging on
				$return = base64_encode(JUri::getInstance());
				$login_url_with_return = JRoute::_('index.php?option=com_users&return=' . $return, false);
				$app->enqueueMessage(JText::_('COM_U3ABOOKING_MUST_LOGIN'), 'notice');
				$app->redirect($login_url_with_return, 403);
			}
		}
		
		if ($this->event)
		{
			// pass in the event id so that it can be injected as data into the form
			$bookingModel->setState('event.id', $this->event->id);
			// and the max number of places that can be booked, so that the form field can be modified
			$bookingModel->setState('event.max_tickets_per_booking', $this->event->max_tickets_per_booking);
			$this->form = $this->get('Form');
		}

		parent::display($tpl);
	}
}