<?php
namespace TYPO3\CMS\Cal\Model;

/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class EventRecModel extends \TYPO3\CMS\Cal\Model\Model
{
    public $parentEvent;
    public $start;
    public $end;
    public $cachedValueArray = array();
    public $initializingCacheValues = false;
    public $row;
    public $myMarkerCache = array();
    
    public function __construct($event, $start, $end)
    {
        parent::__construct($event->serviceKey);
        $this->parentEvent = &$event;
        $this->setStart($start);
        $this->setEnd($end);
        $this->row = &$event->row;
    }
    public function updateWithPiVars(&$piVars)
    {
        $this->parentEvent->updateWithPiVars($piVars);
        $this->parentEvent->markerCache = array();
    }
    public function cloneEvent()
    {
        return $this->parentEvent->cloneEvent();
    }
    
    /**
     * Gets the location of the event.
     * Location does not exist in the default
     * model, only in calexampl3.
     *
     * @return string location.
     */
    public function getLocation()
    {
        return $this->parentEvent->getLocation();
    }
    public function getOrganizer()
    {
        return $this->parentEvent->getOrganizer();
    }
    public function getLocationId()
    {
        return $this->parentEvent->getLocationId();
    }
    public function getOrganizerId()
    {
        return $this->parentEvent->getOrganizerId();
    }
    
    /**
     * Gets the teaser of the event.
     *
     *
     * @return string teaser.
     */
    public function getTeaser()
    {
        return $this->parentEvent->getTeaser();
    }
    public function getLocationLink($view)
    {
        return $this->parentEvent->getLocationLink($view);
    }
    public function getOrganizerLink($view)
    {
        return $this->parentEvent->getOrganizerLink($view);
    }
    
    /**
     * Returns the headerstyle name
     */
    public function getHeaderStyle()
    {
        return $this->parentEvent->getHeaderStyle();
    }
    
    /**
     * Returns the bodystyle name
     */
    public function getBodyStyle()
    {
        return $this->parentEvent->getBodyStyle();
    }
    
    /**
     * Gets the createUserId of the event.
     *
     * @return string create user id.
     */
    public function getCreateUserId()
    {
        return $this->parentEvent->getCreateUserId();
    }
    public function getTimezone()
    {
        return $this->parentEvent->getTimezone();
    }
    public function renderEventForOrganizer()
    {
        return $this->renderEventFor('ORGANIZER');
    }
    public function renderEventForLocation()
    {
        return $this->renderEventFor('LOCATION');
    }
    public function renderEventForDay()
    {
        return $this->renderEventFor('DAY');
    }
    public function renderEventForWeek()
    {
        return $this->renderEventFor('WEEK');
    }
    public function renderEventForAllDay()
    {
        return $this->renderEventFor('ALLDAY');
    }
    public function renderEventForMonth()
    {
        if ($this->isAllday()) {
            return $this->renderEventFor('MONTH_ALLDAY');
        }
        return $this->renderEventFor('MONTH');
    }
    public function renderEventForMiniMonth()
    {
        if ($this->isAllday()) {
            return $this->renderEventFor('MONTH_MINI_ALLDAY');
        }
        return $this->renderEventFor('MONTH_MINI');
    }
    public function renderEventForYear()
    {
        return $this->renderEventFor('year');
    }
    public function renderEvent()
    {
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT###');
    }
    public function renderEventForList($subpartSuffix = 'LIST_ODD')
    {
        return $this->renderEventFor($subpartSuffix);
    }
    public function renderEventFor($viewType, $subpartSuffix = '')
    {
        if ($this->parentEvent->conf ['view.'] ['freeAndBusy.'] ['enable'] == 1) {
            $viewType .= '_FNB';
        }
        if (substr($viewType, - 6) != 'ALLDAY' && ($this->isAllday() || $this->getStart()->format('%Y%m%d') != $this->getEnd()->format('%Y%m%d'))) {
            $subpartSuffix .= 'ALLDAY';
        }
        $hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray('tx_cal_phpicalendar_rec_model', 'eventModelClass', 'model');
        
        foreach ($hookObjectsArr as $hookObj) {
            if (method_exists($hookObj, 'preFillTemplate')) {
                $hookObj->preFillTemplate($this, $viewType, $subpartSuffix);
            }
        }
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_' . strtoupper($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
    }
    public function renderEventPreview()
    {
        $this->parentEvent->isPreview = true;
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_PREVIEW###');
    }
    public function renderTomorrowsEvent()
    {
        $this->parentEvent->isTomorrow = true;
        return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_TOMORROW###');
    }
    public function fillTemplate($subpartMarker)
    {
        $cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'cobj');
        
        $templatePath = $this->parentEvent->conf ['view.'] ['event.'] ['eventModelTemplate'];
        
        $page = Functions::getContent($templatePath);
        
        if ($page == '') {
            return '<h3>calendar: no event model template file found:</h3>' . $templatePath;
        }
        $page = $cObj->getSubpart($page, $subpartMarker);
        if (! $page) {
            return 'could not find the >' . str_replace('###', '', $subpartMarker) . '< subpart-marker in ' . $templatePath;
        }
        $rems = array();
        $sims = array();
        $wrapped = array();
        $this->getMarker($page, $sims, $rems, $wrapped, $this->parentEvent->conf ['alternateRenderingView'] ? $this->parentEvent->conf ['alternateRenderingView'] : '');
        return $this->parentEvent->finish(\TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped));
    }
    public function getSubscriptionMarker(& $template, & $sims, & $rems, &$wrapped, $view)
    {
        return $this->parentEvent->getSubscriptionMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getStartAndEndMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->initLocalCObject();
        array_merge($this->parentEvent->local_cObj->data, $this->getAdditionalValuesAsArray());
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        if ($eventStart->equals($eventEnd)) {
            $sims ['###STARTTIME_LABEL###'] = '';
            $sims ['###ENDTIME_LABEL###'] = '';
            $sims ['###STARTTIME###'] = '';
            $sims ['###ENDTIME###'] = '';
            $this->parentEvent->local_cObj->setCurrentVal($eventStart->format($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
            $sims ['###STARTDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['startdate'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['startdate.']);
            $sims ['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_allday');
            if ($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['dontShowEndDateIfEqualsStartDateAllday'] == 1) {
                $sims ['###ENDDATE###'] = '';
                $sims ['###ENDDATE_LABEL###'] = '';
            } else {
                $this->parentEvent->local_cObj->setCurrentVal($eventEnd->format($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
                $sims ['###ENDDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['enddate'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['enddate.']);
                $sims ['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
            }
        } else {
            if ($this->isAllday()) {
                $sims ['###STARTTIME_LABEL###'] = '';
                $sims ['###STARTTIME###'] = '';
            } else {
                $sims ['###STARTTIME_LABEL###'] = $this->controller->pi_getLL('l_event_starttime');
                $this->parentEvent->local_cObj->setCurrentVal($eventStart->format($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['timeFormat']));
                $sims ['###STARTTIME###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['starttime'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['starttime.']);
            }
            if ($this->isAllday()) {
                $sims ['###ENDTIME_LABEL###'] = '';
                $sims ['###ENDTIME###'] = '';
            } else {
                $sims ['###ENDTIME_LABEL###'] = $this->controller->pi_getLL('l_event_endtime');
                $this->parentEvent->local_cObj->setCurrentVal($eventEnd->format($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['timeFormat']));
                $sims ['###ENDTIME###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['endtime'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['endtime.']);
            }
            
            $this->parentEvent->local_cObj->setCurrentVal($eventStart->format($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
            $sims ['###STARTDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['startdate'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['startdate.']);
            if ($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['dontShowEndDateIfEqualsStartDate'] && $eventEnd->format('%Y%m%d') == $eventStart->format('%Y%m%d')) {
                $sims ['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_date');
                $sims ['###ENDDATE_LABEL###'] = '';
                $sims ['###ENDDATE###'] = '';
            } else {
                $sims ['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_startdate');
                $sims ['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
                $this->parentEvent->local_cObj->setCurrentVal($eventEnd->format($this->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
                $sims ['###ENDDATE###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['enddate'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['enddate.']);
            }
        }
    }
    public function getTitle()
    {
        return $this->parentEvent->getTitle();
    }
    public function getTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getTitleMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getTitleFnbMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getTitleFnbMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getOrganizerMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getOrganizerMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getLocationMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getLocationMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getTeaserMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getTeaserMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getIcsLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getIcsLinkMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getCategoryMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getCategoryMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getCategoryLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getcategoryLinkMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getCategoryIconMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getCategoryIconMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getHeaderstyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims ['###HEADERSTYLE###'] = $this->parentEvent->getHeaderStyle();
    }
    public function getBodystyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $sims ['###BODYSTYLE###'] = $this->parentEvent->getBodyStyle();
    }
    
    /**
     * Returns the calendar style name
     */
    public function getCalendarStyleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getCalendarStyle($template, $sims, $rems, $wrapped, $view);
    }
    public function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getMapMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getAttachmentMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getAttachmentMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getAttachmentUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        return $this->parentEvent->getAttachmentUrlMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $wrapped ['###EVENT_LINK###'] = explode('$5&xs2', $this->parentEvent->getLinkToEvent('$5&xs2', $view, $eventStart->format('%Y%m%d')));
    }
    public function getEventUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $sims ['###EVENT_URL###'] = htmlspecialchars($this->parentEvent->getLinkToEvent('$5&xs2', $view, $eventStart->format('%Y%m%d'), true));
    }
    public function getAbsoluteEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $wrapped ['###ABSOLUTE_EVENT_LINK###'] = explode('$5&xs2', $this->parentEvent->getLinkToEvent('$5&xs2', $view, $eventStart->format('%Y%m%d')));
    }
    public function getStartdate()
    {
        $start = $this->getStart();
        return $start->format(\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf($this->parentEvent->conf));
    }
    public function getEnddate()
    {
        $end = $this->getEnd();
        return $end->format(\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf($this->parentEvent->conf));
    }
    public function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $eventStart = $this->getStart();
        $sims ['###EDIT_LINK###'] = '';
        
        if ($this->parentEvent->isUserAllowedToEdit()) {
            $linkConf = $this->parentEvent->getValuesAsArray();
            if ($this->conf ['view.'] ['enableAjax']) {
                $temp = sprintf($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['editLinkOnClick'], $this->parentEvent->getUid(), $this->parentEvent->getType());
                $linkConf ['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $linkConf ['link_no_cache'] = 0;
            //$linkConf ['link_useCacheHash'] = 0;
            $linkConf ['link_additionalParams'] = '&tx_cal_controller[view]=edit_event&tx_cal_controller[type]=' . $this->parentEvent->getType() . '&tx_cal_controller[uid]=' . $this->parentEvent->getUid() . '&tx_cal_controller[getdate]=' . $eventStart->format('%Y%m%d') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView();
            $linkConf ['link_section'] = 'default';
            $linkConf ['link_parameter'] = $this->parentEvent->conf ['view.'] ['event.'] ['editEventViewPid'] ? $this->parentEvent->conf ['view.'] ['event.'] ['editEventViewPid'] : $GLOBALS ['TSFE']->id;
            
            $this->parentEvent->initLocalCObject($linkConf);
            $this->parentEvent->local_cObj->setCurrentVal($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['editIcon']);
            $sims ['###EDIT_LINK###'] = $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['editLink'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['editLink.']);
        }
        if ($this->parentEvent->isUserAllowedToDelete()) {
            // controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
            $linkConf = $this->parentEvent->getValuesAsArray();
            if ($this->parentEvent->conf ['view.'] ['enableAjax']) {
                $temp = sprintf($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['deleteLinkOnClick'], $this->parentEvent->getUid(), $this->parentEvent->getType());
                $linkConf ['link_ATagParams'] = ' onclick="' . $temp . '"';
            }
            $linkConf ['link_no_cache'] = 0;
            //$linkConf ['link_useCacheHash'] = 0;
            $linkConf ['link_additionalParams'] = '&tx_cal_controller[view]=delete_event&tx_cal_controller[type]=' . $this->parentEvent->getType() . '&tx_cal_controller[uid]=' . $this->parentEvent->getUid() . '&tx_cal_controller[getdate]=' . $eventStart->format('%Y%m%d') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView();
            $linkConf ['link_section'] = 'default';
            $linkConf ['link_parameter'] = $this->parentEvent->conf ['view.'] ['event.'] ['deleteEventViewPid'] ? $this->parentEvent->conf ['view.'] ['event.'] ['deleteEventViewPid'] : $GLOBALS ['TSFE']->id;
            
            $this->parentEvent->initLocalCObject($linkConf);
            $this->parentEvent->local_cObj->setCurrentVal($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['deleteIcon']);
            $sims ['###EDIT_LINK###'] .= $this->parentEvent->local_cObj->cObjGetSingle($this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['deleteLink'], $this->parentEvent->conf ['view.'] [$view . '.'] ['event.'] ['deleteLink.']);
        }
    }
    public function getMoreLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getMoreLinkMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getStartdateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartAndEndMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getEnddateMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getStarttimeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getEndtimeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getDescriptionStriptagsMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getDescriptionStriptagsMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array())
    {
        $rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'rightscontroller');
        if (! $rightsObj->isViewEnabled('edit_event')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }
        $editOffset = $this->parentEvent->conf ['rights.'] ['edit.'] ['event.'] ['timeOffset'] * 60;
        
        if ($feUserUid == '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isEventOwner = $this->parentEvent->isEventOwner($feUserUid, $feGroupsArray);
        $isSharedUser = $this->parentEvent->isSharedUser($feUserUid, $feGroupsArray);
        if ($rightsObj->isAllowedToEditStartedEvent()) {
            $eventHasntStartedYet = true;
        } else {
            $temp = new \TYPO3\CMS\Cal\Model\CalDate();
            $temp->setTZbyId('UTC');
            $temp->addSeconds($editOffset);
            $eventStart = $this->getStart();
            $eventHasntStartedYet = $eventStart->after($temp);
        }
        $isAllowedToEditEvent = $rightsObj->isAllowedToEditEvent();
        $isAllowedToEditOwnEventsOnly = $rightsObj->isAllowedToEditOnlyOwnEvent();
        
        if ($isAllowedToEditOwnEventsOnly) {
            return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
        }
        return $isAllowedToEditEvent && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
    }
    public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array())
    {
        $rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'rightscontroller');
        if (! $rightsObj->isViewEnabled('delete_event')) {
            return false;
        }
        if ($rightsObj->isCalAdmin()) {
            return true;
        }
        $deleteOffset = $this->parentEvent->conf ['rights.'] ['delete.'] ['event.'] ['timeOffset'] * 60;
        if ($feUserUid == '') {
            $feUserUid = $rightsObj->getUserId();
        }
        if (empty($feGroupsArray)) {
            $feGroupsArray = $rightsObj->getUserGroups();
        }
        $isEventOwner = $this->parentEvent->isEventOwner($feUserUid, $feGroupsArray);
        $isSharedUser = $this->parentEvent->isSharedUser($feUserUid, $feGroupsArray);
        if ($rightsObj->isAllowedToDeleteStartedEvents()) {
            $eventHasntStartedYet = true;
        } else {
            $temp = new \TYPO3\CMS\Cal\Model\CalDate();
            $temp->setTZbyId('UTC');
            $temp->addSeconds($deleteOffset);
            $eventStart = $this->getStart();
            $eventHasntStartedYet = $eventStart->after($temp);
        }
        $isAllowedToDeleteEvents = $rightsObj->isAllowedToDeleteEvents();
        $isAllowedToDeleteOwnEventsOnly = $rightsObj->isAllowedToDeleteOnlyOwnEvents();
        
        if ($isAllowedToDeleteOwnEventsOnly) {
            return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
        }
        return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
    }
    public function __toString()
    {
        return 'Phpicalendar ' . (is_object($this) ? 'object' : 'something') . ': ' . implode(',', $this->parentEvent->row);
    }
    public function getAttendees()
    {
        return $this->parentEvent->getAttendees();
    }
    public function getAttendeeMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getAttendeeMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getLinkToEvent($linktext, $view, $date, $urlOnly = false)
    {
        return $this->parentEvent->getLinkToEvent($linktext, $view, $date, $urlOnly);
    }
    public function getEventIdMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $start = $this->getStart();
        $sims ['###EVENT_ID###'] = $this->parentEvent->getType() . $this->parentEvent->getUid() . $start->format('%Y%m%d%H%M');
    }
    public function getGuidMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getGuidMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getDtstampMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getDtstampMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getCruserNameMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getCruserNameMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getCalendarTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getCalendarTitleMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getNow()
    {
        return $this->parentEvent->getNow();
    }
    public function getToday()
    {
        return $this->parentEvent->getToday();
    }
    public function getImageMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getImageMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getDescriptionMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getHeadingMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getHeadingMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getEditPanelMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getEditPanelMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getTopMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getTopMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getLengthMarker(& $template, & $sims, & $rems, & $wrapped, $view)
    {
        $this->parentEvent->getLengthMarker($template, $sims, $rems, $wrapped, $view);
    }
    public function getUid()
    {
        return $this->parentEvent->getUid();
    }
    public function isAllday()
    {
        return $this->parentEvent->isAllday();
    }
    public function getEventOwner()
    {
        return $this->parentEvent->getEventOwner();
    }
    public function getCalendarUid()
    {
        return $this->parentEvent->getCalendarUid();
    }
    public function getType()
    {
        return $this->parentEvent->getType();
    }
    public function getEventType()
    {
        return $this->parentEvent->getEventType();
    }
    public function getCount()
    {
        return $this->parentEvent->getCount();
    }
    public function getValuesAsArray()
    {
        if ($this->initializingCacheValues) {
            return $this->parentEvent->row;
        }
        
        if (! count($this->cachedValueArray)) {
            // set locking variable
            $this->initializingCacheValues = true;
            $values = $this->parentEvent->getValuesAsArray();
            
            $additionalValues = $this->getAdditionalValuesAsArray();
            $mergedValues = array_merge($values, $additionalValues);
            
            // now cache the result to win some ms
            $this->cachedValueArray = (array) $mergedValues;
            $this->initializingCacheValues = false;
        }
        return $this->cachedValueArray;
    }
    public function getAdditionalValuesAsArray()
    {
        $values = parent::getAdditionalValuesAsArray();
        $values ['start'] = $this->getStartAsTimestamp();
        $values ['end'] = $this->getEndAsTimestamp();
        $values ['parent_startdate'] = $this->parentEvent->start->format('%Y%m%d');
        $values ['parent_enddate'] = $this->parentEvent->end->format('%Y%m%d');
        $values ['parent_starttime'] = $this->parentEvent->start->getHour() * 60 + $this->parentEvent->start->getMinute();
        $values ['parent_endtime'] = $this->parentEvent->end->getHour() * 60 + $this->parentEvent->end->getMinute();
        return $values;
    }
    public function getCategories()
    {
        return $this->parentEvent->categories;
    }
    public function getUntil()
    {
        return $this->parentEvent->getUntil();
    }
}
