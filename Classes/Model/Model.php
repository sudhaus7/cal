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

use TYPO3\CMS\Cal\Model\Pear\Date\Calc;

/**
 * Base model for the calendar.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class Model extends \TYPO3\CMS\Cal\Model\BaseModel
{
    public $row;
    public $isClone = false;
    public $tstamp;
    public $sequence = 1;
    public $title;
    public $organizer;
    public $location;
    public $content;
    public $start;
    public $end;
    public $allday = 0;
    public $timezone;
    public $calnumber = 1;
    public $calname;
    public $calendarUid;
    public $url;
    public $alarmdescription;
    public $summary;
    public $description;
    public $overlap = 1;
    public $_class;
    public $until;
    public $freq = '';
    public $reccuring_end;
    public $cnt;
    public $bysecond = array();
    public $byminute = array();
    public $byhour = array();
    public $byday = array();
    public $byweekno = array();
    public $bymonth = array();
    public $byyearday = array();
    public $bymonthday = array();
    public $byweekday = array();
    public $bysetpos = array();
    public $wkst = '';
    public $rdateType = '';
    public $rdate = '';
    public $rdateValues = array();
    public $displayend;
    public $spansday;
    public $categories = array();
    public $categoriesAsString;
    public $categoryUidsAsArray;
    public $location_id = 0;
    public $organizer_id = 0;
    public $locationLink;
    public $organizerLink;
    public $locationPage;
    public $organizerPage;
    public $organizerObject;
    public $locationObject;
    public $exception_single_ids = array();
    public $notifyUserIds = array();
    public $exceptionGroupIds = array();
    public $notifyGroupIds = array();
    public $creatorUserIds = array();
    public $creatorGroupIds = array();
    public $exceptionEvents = array();
    public $editable = false;
    public $headerstyle = 'default_catheader'; // '#557CA3';//'#0000ff';
    public $bodystyle = 'default_catbody'; // ''#6699CC';//'#ccffcc';
    public $crdate = 0;
    public $deviationDates;
    
    /* new */
    public $event_type;
    public $page;
    public $ext_url;
    /* new */
    public $externalPlugin = 0;
    public $sharedUsers = array();
    public $sharedGroups = array();
    public $eventOwner;
    public $attendees = array();
    public $status = 0;
    public $priority = 0;
    public $completetd = 0;
    const EVENT_TYPE_DEFAULT = 0;
    const EVENT_TYPE_SHORTCUT = 1;
    const EVENT_TYPE_EXTERNAL = 2;
    const EVENT_TYPE_MEETING = 3;
    const EVENT_TYPE_TODO = 4;
    
    /**
     * Constructor.
     *
     * @param $serviceKey String
     *        	serviceKey for this model
     */
    public function __construct($serviceKey)
    {
        $this->setObjectType('event');
        parent::__construct($serviceKey);
    }
    
    /**
     * Returns the timestamp value.
     *
     * @return Integer timestamp.
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
    
    /**
     * Sets the timestamp value.
     *
     * @param $timestamp Integer
     */
    public function setTstamp($timestamp)
    {
        $this->tstamp = $timestamp;
    }
    
    /**
     * Returns the sequence value.
     *
     * @return Array sequence.
     */
    public function getSequence()
    {
        return $this->sequence;
    }
    
    /**
     * Sets the sequence value.
     *
     * @param $sequence Array
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }
    
    /**
     * Sets the event organizer.
     *
     * @param $organizer String
     *        	of the event.
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;
    }
    
    /**
     * Returns the event organizer.
     *
     * @return String organizer of the event.
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }
    
    /**
     * Sets the event title.
     *
     * @param $title String
     *        	of the event.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * Returns the event title.
     *
     * @return String title of the event.
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Sets the event creation time.
     *
     * @param $timestamp Integer
     *        	the event creation.
     */
    public function setCreationDate($timestamp)
    {
        $this->crdate = $timestamp;
    }
    
    /**
     * Returns timestamp of the event creation.
     *
     * @return Integer of the event creation.
     */
    public function getCreationDate()
    {
        return $this->crdate;
    }
    
    /**
     * Returns the rendered event.
     *
     * @return String event.
     */
    public function renderEvent()
    {
        $cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'cobj');
        $d = nl2br($cObj->parseFunc($this->getDescription(), $this->conf ['parseFunc.']));
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        return '<h3>' . $this->getTitle() . '</h3><font color="#000000"><ul>' . '<li>Start: ' . $eventStart->format('%H:%M') . '</li>' . '<li>End: ' . $eventEnd->format('%H:%M') . '</li>' . '<li> Organizer: ' . $this->getOrganizer() . '</li>' . '<li>Location: ' . $this->getLocation() . '</li>' . '<li>Description: ' . $d . '</li></ul></font>';
    }
    
    /**
     * Returns the rendered event for allday.
     *
     * @return String event for allday -> the title.
     */
    public function renderEventForAllDay()
    {
        return $this->getTitle();
    }
    
    /**
     * Returns the rendered event for day.
     *
     * @return String event for day -> the title.
     */
    public function renderEventForDay()
    {
        return $this->title;
    }
    
    /**
     * Returns the rendered event for week.
     *
     * @return String event for week -> the title.
     */
    public function renderEventForWeek()
    {
        return $this->title;
    }
    
    /**
     * Returns the rendered event month day.
     *
     * @return String event for month -> the title.
     */
    public function renderEventForMonth()
    {
        return $this->title;
    }
    
    /**
     * Returns the rendered event month day for a mini month view.
     *
     * @return String event for a mini month -> the title.
     */
    public function renderEventForMiniMonth()
    {
        return $this->title;
    }
    
    /**
     * Returns the rendered event for year.
     *
     * @return String event for year -> the title.
     */
    public function renderEventForYear()
    {
        return $this->title;
    }
    
    /**
     * Returns the location value.
     *
     * @return String location.
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * Sets the event location value.
     *
     * @param $location String
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }
    
    /**
     * Returns the location link value.
     *
     * @return String location link.
     */
    public function getLocationLinkUrl()
    {
        return $this->locationLink;
    }
    
    /**
     * Sets the event location link value.
     *
     * @param $locationLink String
     *        	link.
     */
    public function setLocationLinkUrl($locationLink)
    {
        $this->locationLink = $locationLink;
    }
    
    /**
     * Sets the event location page value.
     *
     * @param $page Integer
     *        	page.
     */
    public function setLocationPage($page)
    {
        $this->locationPage = $page;
    }
    
    /**
     * Returns the locationPage.
     *
     * @return Integer pid to link the location to
     */
    public function getLocationPage()
    {
        return $this->locationPage;
    }
    
    /**
     * Returns the startdate object.
     *
     * @return Integer startdate timeObject
     */
    public function getStart()
    {
        return $this->start;
    }
    
    /**
     * Returns the enddate object.
     *
     * @return Integer enddate timeObject
     */
    public function getEnd()
    {
        if (! $this->end) {
            $this->setEnd($this->getStart());
            $this->end->addSeconds($this->conf ['view.'] ['event.'] ['event.'] ['defaultEventLength']);
        }
        return $this->end;
    }
    
    /**
     * Sets the event start.
     *
     * @param $start Object
     *        	object
     */
    public function setStart($start)
    {
        $this->start = new \TYPO3\CMS\Cal\Model\CalDate();
        $this->start->copy($start);
        $this->row ['start_date'] = $start->format('%Y%m%d');
        $this->row ['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
    }
    
    /**
     * Sets the event end.
     *
     * @param $end Object
     *        	object
     */
    public function setEnd($end)
    {
        $this->end = new \TYPO3\CMS\Cal\Model\CalDate();
        $this->end->copy($end);
        $this->row ['end_date'] = $end->format('%Y%m%d');
        $this->row ['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
    }
    
    /**
     * Returns the startdate as unix timestamp.
     *
     * @return Integer startdate as unix timestamp
     */
    public function getStartAsTimestamp()
    {
        $start = &$this->getStart();
        return $start->getDate(DATE_FORMAT_UNIXTIME);
    }
    
    /**
     * Returns the enddate as unix timestamp.
     *
     * @return Integer enddate as unix timestamp
     */
    public function getEndAsTimestamp()
    {
        $end = &$this->getEnd();
        return $end->getDate(DATE_FORMAT_UNIXTIME);
    }
    
    /**
     * Returns the ? value.
     *
     * @return ? ?
     *         @TODO field is missing
     */
    public function getConfirmed()
    {
        return;
    }
    
    /**
     * Returns the cal recu value.
     *
     * @return Array ? - empty array
     *         @TODO What is that for?
     */
    public function getCalRecu()
    {
        return array();
    }
    
    /**
     * Returns the cal number value.
     *
     * @return String calnumber
     */
    public function getCalNumber()
    {
        return $this->calnumber;
    }
    
    /**
     * Sets the calnumber.
     *
     * @param $calnumber String
     */
    public function setCalNumber($calnumber)
    {
        $this->calnumber = $calnumber;
    }
    
    /**
     * Returns the calendar uid.
     *
     * @return Integer calendar uid
     */
    public function getCalendarUid()
    {
        return $this->calendarUid;
    }
    
    /**
     * Sets the calendar uid.
     *
     * @param $uid Integer
     *        	uid.
     */
    public function setCalendarUid($uid)
    {
        $this->calendarUid = $uid;
    }
    
    /**
     * Returns the calendar object
     *
     * @return tx_cal_calendar_model calendar object
     */
    public function getCalendarObject()
    {
        if (! $this->calendarObject) {
            $modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'modelcontroller');
            $this->calendarObject = $modelObj->findCalendar($this->getCalendarUid());
        }
        
        return $this->calendarObject;
    }
    
    /**
     * Returns the calendar name.
     *
     * @return String calendar name
     */
    public function getCalName()
    {
        return $this->calname;
    }
    
    /**
     * Sets the calendar name.
     *
     * @param $name String
     *        	name.
     */
    public function setCalName($calname)
    {
        $this->calname = $calname;
    }
    public function getOverlap()
    {
        return $this->overlap;
    }
    public function setOverlap($overlap)
    {
        $this->overlap = $overlap;
    }
    public function getTimezone()
    {
        return $this->timezone;
    }
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }
    public function getDuration()
    {
        return $this->end->getTime() - $this->start->getTime();
    }
    public function getFormatedDurationString($duration)
    {
        $durationString = '';
        if ($duration < 0) {
            $durationString .= '-';
        }
        $duration = abs($duration);
        $durationString .= 'P';
        $rest = $duration % (60 * 60 * 24);
        $days = ($duration - $rest) / (60 * 60 * 24);
        if ($days > 0) {
            $durationString .= $days . 'D';
        }
        $durationString .= 'T';
        $rest2 = $rest % (60 * 60);
        $hours = ($rest - $rest2) / (60 * 60);
        if ($hours > 0) {
            $durationString .= $hours . 'H';
        }
        
        $rest3 = $rest2 % (60);
        $minutes = ($rest2 - $rest3) / (60);
        if ($minutes > 0) {
            $durationString .= $minutes . 'M';
        }
        
        if ($rest3 > 0) {
            $durationString .= $rest3 . 'M';
        }
        return $durationString;
    }
    public function isAllday()
    {
        return $this->allday;
    }
    public function getAllday()
    {
        return $this->allday;
    }
    public function setAllday($boolean)
    {
        $this->allday = $boolean;
    }
    public function getRecurringRule()
    {
        if ($this->freq != 'none' && $this->freq != '') {
            $return = array();
            $return ['FREQ'] = $this->freq;
            $return ['INTERVAL'] = $this->interval;
            return $return;
        }
        return;
    }
    public function setRecur($recur = array())
    {
        // TODO?
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function setUrl($url)
    {
        $this->url = $url;
    }
    public function getVAlarmDescription()
    {
        return $this->alarmdescription;
    }
    public function setVAlarmDescription($alarmdescription)
    {
        $this->alarmdescription = $alarmdescription;
    }
    public function isClone()
    {
        return $this->isClone;
    }
    public function setIsClone($boolean)
    {
        $this->isClone = $boolean;
    }
    public function getRecurrance()
    {
        $a = array();
        $a ['tzid'] = $this->getTimezone();
        $a ['date'] = $this->startdate;
        $a ['time'] = $this->starthour;
        return $a;
    }
    public function getByMonth()
    {
        return $this->bymonth;
    }
    public function setByMonth($bymonth)
    {
        if ($bymonth != '') {
            $this->bymonth = explode(',', $bymonth);
        }
        if (strtoupper($bymonth) == 'ALL' || in_array('all', $this->bymonth)) {
            $this->bymonth = array(
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10,
                    11,
                    12
            );
        }
    }
    public function getByDay()
    {
        return $this->byday;
    }
    public function setByDay($byday)
    {
        $byday = strtoupper($byday);
        if ($byday != '') {
            $this->byday = explode(',', $byday);
        }
        
        if (strtoupper($byday) == 'ALL' || in_array('all', $this->byday)) {
            $this->byday = array(
                    'MO',
                    'TU',
                    'WE',
                    'TH',
                    'FR',
                    'SA',
                    'SU'
            );
        }
    }
    public function getByMonthDay()
    {
        return $this->bymonthday;
    }
    public function setByMonthday($bymonthday)
    {
        if ($bymonthday != '') {
            $this->bymonthday = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $bymonthday, 1);
        }
        if (strtoupper($bymonthday) == 'ALL' || in_array('all', $this->bymonthday)) {
            $this->bymonthday = array(
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10,
                    11,
                    12,
                    13,
                    14,
                    15,
                    16,
                    17,
                    18,
                    19,
                    20,
                    21,
                    22,
                    23,
                    24,
                    25,
                    26,
                    27,
                    28,
                    29,
                    30,
                    31
            );
        }
    }
    public function getByWeekDay()
    {
        return $this->byweekday;
    }
    public function setByWeekDay($byweekday)
    {
        $this->byweekday = explode(',', $byweekday);
    }
    public function getByWeekNo()
    {
        return $this->byweekno;
    }
    public function setByWeekNo($byweekno)
    {
        $this->byweekno = explode(',', $byweekno);
    }
    public function getByMinute()
    {
        return $this->byminute;
    }
    public function setByMinute($byminute)
    {
        $this->byminute = explode(',', $byminute);
    }
    public function getByHour()
    {
        return $this->byhour;
    }
    public function setByHour($byhour)
    {
        $this->byhour = explode(',', $byhour);
    }
    public function getBySecond()
    {
        return $this->bysecond;
    }
    public function setBySecond($bysecond)
    {
        $this->bysecond = explode(',', $bysecond);
    }
    public function getByYearDay()
    {
        return $this->byyearday;
    }
    public function setByYearDay($byyearday)
    {
        $this->byyearday = explode(',', $byyearday);
    }
    public function getBySetPos()
    {
        return $this->bysetpos;
    }
    public function setBySetPos($bysetpos)
    {
        $this->bysetpos = $bysetpos;
    }
    public function getWkst()
    {
        return $this->wkst;
    }
    public function setWkst($wkst)
    {
        $this->wkst = $wkst;
    }
    public function getInterval()
    {
        return $this->interval;
    }
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }
    public function getSummary()
    {
        return $this->summary;
    }
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    public function getClass()
    {
        return $this->_class;
    }
    public function setClass($class)
    {
        $this->_class = $class;
    }
    public function getDisplayEnd()
    {
        return $this->displayend;
    }
    public function setDisplayEnd($displayend)
    {
        $this->displayend = $displayend;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function setContent($t)
    {
        $this->content = $t;
    }
    
    /**
     * Returns
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Sets the discription attribute
     *
     * @param $description string
     *        	the event
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    
    /**
     * Returns the until date object
     */
    public function getUntil()
    {
        return $this->until;
    }
    
    /**
     * Sets the until object.
     *
     * @param $until object
     *        	object
     */
    public function setUntil($until)
    {
        $this->until = $until;
    }
    public function getFreq()
    {
        return $this->freq;
    }
    
    /**
     * Sets the recurring frequency
     */
    public function setFreq($freq)
    {
        $this->freq = $freq;
    }
    
    /**
     * Returns how often a recurring event is supposed to recurr as max
     */
    public function getCount()
    {
        return $this->cnt;
    }
    
    /**
     * Sets how often a recurring event is supposed to recurr as max
     *
     * @param $count int
     *        	a recurring event is supposed to recurr as max
     */
    public function setCount($count)
    {
        $this->cnt = $count;
    }
    
    /**
     * Returns the rdate value.
     *
     * @return String rdate value
     */
    public function getRdate()
    {
        return $this->rdate;
    }
    
    /**
     * Sets the rdate value.
     *
     * @param $rdate String
     *        	value
     */
    public function setRdate($rdate)
    {
        $this->rdate = strtoupper($rdate);
    }
    
    /**
     * Returns the rdate value as array split by comma.
     *
     * @return String rdate value as array split by comma.
     */
    public function getRdateValues()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->rdate, 1);
    }
    
    /**
     * Sets the rdate value.
     *
     * @param $rdate String
     *        	value
     */
    public function setRdateValues($rdateArray)
    {
        $this->rdate = implode(',', $rdateArray);
    }
    
    /**
     * Returns the rdate type value.
     *
     * @return String rdate type value
     */
    public function getRdateType()
    {
        return $this->rdateType;
    }
    
    /**
     * Sets the rdate type value.
     *
     * @param $rdateType String
     *        	type value
     */
    public function setRdateType($rdateType)
    {
        $this->rdateType = $rdateType;
    }
    
    /**
     * Returns TRUE if the events lasts the whole day
     */
    public function getSpansDay()
    {
        return $this->spansday;
    }
    
    /**
     * Sets the spansday attribute
     *
     * @param $spansday boolean
     *        	the event lasts the whole day
     */
    public function setSpansDay($spansday)
    {
        $this->spansday = $spansday;
    }
    
    /**
     * Returns the categories (array)
     */
    public function getCategories()
    {
        return $this->categories;
    }
    
    /**
     * Sets the categories
     *
     * @param $categories Array
     *        	representation of the categories
     */
    public function setCategories($categories)
    {
        if (is_array($categories)) {
            $this->categories = $categories;
        }
    }
    
    /**
     * Adds an event to the exceptionEvents array
     *
     * @param $ex_events object
     *        	this class (tx_cal_model)
     */
    public function addExceptionEvent($ex_event)
    {
        array_push($this->exceptionEvents, $ex_event);
    }
    
    /**
     * Sets the exceptionEvents
     *
     * @param $ex_events array
     *        	exception events
     */
    public function setExceptionEvents($ex_events)
    {
        $this->exceptionEvents = $ex_events;
    }
    
    /**
     * Returns the exceptionEvents array
     */
    public function getExceptionEvents()
    {
        return $this->exceptionEvents;
    }
    
    /**
     * Sets the editable value
     *
     * @param $editable boolean
     *        	the event should be editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
    }
    
    /**
     * Returns TRUE if this event is editable
     */
    public function getEditable()
    {
        return $this->editable;
    }
    
    /**
     * Sets the organizer_id
     *
     * @param $id int
     *        	id
     */
    public function setOrganizerId($id)
    {
        $this->organizer_id = $id;
    }
    
    /**
     * Returns the organizer_id
     */
    public function getOrganizerId()
    {
        return $this->organizer_id;
    }
    
    /**
     * Returns the organizer object.
     */
    public function getOrganizerObject()
    {
        if (! $this->organizerObject) {
            $confArr = unserialize($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
            $useOrganizerStructure = ($confArr ['useOrganizerStructure'] ? $confArr ['useOrganizerStructure'] : 'tx_cal_organizer');
            $modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'modelcontroller');
            $this->organizerObject = $modelObj->findOrganizer($this->getOrganizerId(), $useOrganizerStructure, $this->conf ['pidList']);
        }
        
        return $this->organizerObject;
    }
    
    /**
     * Sets the organizerLink
     *
     * @param $id string
     *        	link to an organizer
     */
    public function setOrganizerLinkUrl($id)
    {
        $this->organizerLink = $id;
    }
    
    /**
     * Return the organizerLink.
     * A html link to an organizer
     */
    public function getOrganizerLinkUrl()
    {
        return $this->organizerLink;
    }
    
    /**
     * Return the organizerpage.
     * The pid to link the organizer to
     */
    public function getOrganizerPage()
    {
        return $this->organizerPage;
    }
    
    /**
     * Sets the organizerPage
     *
     * @param $pid int
     *        	to link the organizer to
     */
    public function setOrganizerPage($pid)
    {
        $this->organizerPage = $pid;
    }
    
    /**
     * Sets the location_id
     *
     * @param $id int
     *        	id
     */
    public function setLocationId($id)
    {
        $this->location_id = $id;
    }
    
    /**
     * Returns the location_id
     */
    public function getLocationId()
    {
        return $this->location_id;
    }
    
    /**
     * Returns the location object.
     */
    public function getLocationObject()
    {
        if (! $this->locationObject) {
            $confArr = unserialize($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
            $useLocationStructure = ($confArr ['useLocationStructure'] ? $confArr ['useLocationStructure'] : 'tx_cal_location');
            $modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic', 'modelcontroller');
            $this->locationObject = $modelObj->findLocation($this->getLocationId(), $useLocationStructure, $this->conf ['pidList']);
        }
        return $this->locationObject;
    }
    
    /**
     * Adds an id to the exception_single_ids array
     *
     * @param $id int
     *        	to be added
     */
    public function addExceptionSingleId($id)
    {
        $this->exception_single_ids [] = $id;
    }
    
    /**
     * Returns the exception_single_ids array
     */
    public function getExceptionSingleIds()
    {
        return $this->exception_single_ids;
    }
    
    /**
     * Sets the exception_single_ids array
     *
     * @param $idArray Array
     *        	exception single ids
     */
    public function setExceptionSingleIds($idArray)
    {
        $this->exception_single_ids = $idArray;
    }
    
    /**
     * Adds an id to the notifyUserIds array
     *
     * @param $id int
     *        	to be added
     */
    public function addNotifyUser($id)
    {
        $this->notifyUserIds [] = $id;
    }
    
    /**
     * Adds an category object to the category array
     *
     * @param $category object
     *        	to be added
     */
    public function addCategory($category)
    {
        $this->categories [] = $category;
    }
    
    /**
     * Returns the notifyUserIds array
     */
    public function getNotifyUserIds()
    {
        return $this->notifyUserIds;
    }
    
    /**
     * Adds am id to the exceptionGroupIds array
     *
     * @param $id int
     *        	to be added
     */
    public function addExceptionGroupId($id)
    {
        if ($id > 0) {
            array_push($this->exceptionGroupIds, $id);
        }
    }
    
    /**
     * Returns the exceptionGroupIds array
     */
    public function getExceptionGroupIds()
    {
        return $this->exceptionGroupIds;
    }
    
    /**
     * Sets the exceptionGroupIds array
     *
     * @param $idArray Array
     *        	exception group ids
     */
    public function setExceptionGroupIds($idArray)
    {
        $this->exceptionGroupIds = $idArray;
    }
    
    /**
     * Adds an id to the notifyGroupIds array
     *
     * @param $id int
     *        	to be added
     */
    public function addNotifyGroup($id)
    {
        if ($id > 0) {
            $this->notifyGroupIds [] = $id;
        }
    }
    
    /**
     * Returns the notifyGroupIds array
     */
    public function getNotifyGroupIds()
    {
        return $this->notifyGroupIds;
    }
    
    /**
     * Adds an id to the creatorUserIds array
     *
     * @param $id int
     *        	to be added
     */
    public function addCreatorUserId($id)
    {
        array_push($this->creatorUserIds, $id);
    }
    
    /**
     * Returns the creatorUserIds array
     */
    public function getCreatorUserIds()
    {
        return $this->creatorUserIds;
    }
    
    /**
     * Adds an id to the creatorGroupIds array
     *
     * @param $id int
     *        	to be added
     */
    public function addCreatorGroupId($id)
    {
        $this->creatorGroupIds [] = $id;
    }
    
    /**
     * Returns the creatorGroupIds array
     */
    public function getCreatorGroupIds()
    {
        return $this->creatorGroupIds;
    }
    
    /**
     * Sets the headerstyle
     *
     * @param $style String
     *        	name
     */
    public function setHeaderStyle($style)
    {
        if ($style != '') {
            $this->headerstyle = $style;
        }
    }
    
    /**
     * Returns the headerstyle name
     */
    public function getHeaderStyle()
    {
        return $this->headerstyle;
    }
    
    /**
     * Sets the bodystyle
     *
     * @param $style String
     *        	name
     */
    public function setBodyStyle($style)
    {
        if ($style != '') {
            $this->bodystyle = $style;
        }
    }
    
    /**
     * Returns the bodystyle name
     */
    public function getBodyStyle()
    {
        return $this->bodystyle;
    }
    
    /* new */
    public function setPage($t)
    {
        $this->page = $t;
    }
    public function getPage()
    {
        return $this->page;
    }
    public function setExtUrl($t)
    {
        $this->ext_url = $t;
    }
    public function getEventType()
    {
        return $this->event_type;
    }
    public function setEventType($t)
    {
        $this->event_type = $t;
    }
    /* new */
    public function search($pidList = '')
    {
    }
    public function addSharedUser($id)
    {
        $this->sharedUsers [] = $id;
    }
    public function addSharedGroup($id)
    {
        $this->sharedGroups [] = $id;
    }
    public function getSharedUsers()
    {
        return ($this->sharedUsers);
    }
    public function getSharedGroups()
    {
        return ($this->sharedGroups);
    }
    public function setSharedUsers($userIds)
    {
        $this->sharedUsers = $userIds;
    }
    public function setSharedGroups($groupIds)
    {
        $this->sharedGroups = $groupIds;
    }
    public function setEventOwner($owner)
    {
        $this->eventOwner = $owner;
    }
    public function getEventOwner()
    {
        return $this->eventOwner;
    }
    public function isEventOwner($userId, $groupIdArray)
    {
        if (is_array($this->eventOwner ['fe_users']) && in_array($userId, $this->eventOwner ['fe_users'])) {
            return true;
        }
        foreach ($groupIdArray as $id) {
            if (is_array($this->eventOwner ['fe_groups']) && in_array($id, $this->eventOwner ['fe_groups'])) {
                return true;
            }
        }
        return false;
    }
    public function isSharedUser($userId, $groupIdArray)
    {
        if (is_array($this->getSharedUsers()) && in_array($userId, $this->getSharedUsers())) {
            return true;
        }
        foreach ($groupIdArray as $id) {
            if (is_array($this->getSharedGroups()) && in_array($id, $this->getSharedGroups())) {
                return true;
            }
        }
        
        return false;
    }
    public function getAdditionalValuesAsArray()
    {
        $values = array();
        
        $values ['page'] = $this->getPage();
        $values ['type'] = $this->getEventType();
        $values ['model_type'] = $this->getType();
        $values ['intrval'] = $this->getInterval();
        $values ['cnt'] = $this->getCount();
        
        $until = $this->getUntil();
        if (is_object($until)) {
            $values ['until'] = $until->format('%Y%m%d');
        } else {
            $values ['until'] = '00000101';
        }
        $values ['category_headerstyle'] = $this->getHeaderstyle();
        $values ['category_bodystyle'] = $this->getBodystyle();
        $start = &$this->getStart();
        $values ['start_date'] = $start->format('%Y%m%d');
        $values ['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
        $values ['start'] = $this->getStartAsTimestamp();
        $end = &$this->getEnd();
        $values ['end_date'] = $end->format('%Y%m%d');
        $values ['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
        $values ['end'] = $this->getEndAsTimestamp();
        $values ['allday'] = $this->isAllday();
        $values ['calendar_id'] = $this->getCalendarUid();
        $values ['category_string'] = $this->getCategoriesAsString(false);
        
        return $values;
    }
    public function getCategoriesAsString($asLink = true)
    {
        /*
         * if($this->categoriesAsString){ return $this->categoriesAsString; }
         */
        $this->categoriesAsString = array();
        $rememberCats = array();
        $objectType = $this->getObjectType();
        
        if (count($this->categories)) {
            foreach ($this->categories as $categoryObject) {
                if (is_object($categoryObject)) {
                    if (in_array($categoryObject->getUid(), $rememberCats)) {
                        continue;
                    }
                    
                    $rememberCats [] = $categoryObject->getUid();
                    // init object and hand over the data of the category as fake DB values
                    $this->initLocalCObject($categoryObject->getValuesAsArray());
                    $categoryTitle = $this->local_cObj->stdWrap($categoryObject->getTitle(), $this->conf ['view.'] [$this->conf ['view'] . '.'] [$objectType . '.'] ['categoryLink_stdWrap.']);
                    
                    if ($asLink) {
                        $headerstyle = $categoryObject->getHeaderStyle();
                        $this->local_cObj->data ['link_ATagParams'] = $headerstyle != '' ? ' class="' . $headerstyle . '"' : '';
                        $parameter ['category'] = $categoryObject->getUid();
                        $parameter ['offset'] = null;
                        
                        $this->controller->getParametersForTyposcriptLink($this->local_cObj->data, $parameter, $this->conf ['cache'], $this->conf ['clear_anyway']);
                        $this->local_cObj->setCurrentVal($categoryTitle);
                        $this->categoriesAsString [] = $this->local_cObj->cObjGetSingle($this->conf ['view.'] [$this->conf ['view'] . '.'] [$objectType . '.'] ['categoryLink'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$objectType . '.'] ['categoryLink.']);
                    } else {
                        $this->categoriesAsString [] = $categoryTitle;
                    }
                }
            }
        }
        // reset the object
        $this->initLocalCObject();
        return implode($this->local_cObj->cObjGetSingle($this->conf ['view.'] [$this->conf ['view'] . '.'] [$objectType . '.'] ['categoryLink_splitChar'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$objectType . '.'] ['categoryLink_splitChar.']), $this->categoriesAsString);
    }
    public function getCategoryUidsAsArray()
    {
        if ($this->categoryUidsAsArray) {
            return $this->categoryUidsAsArray;
        }
        $first = true;
        $this->categoryUidsAsArray = array();
        $categories = &$this->getCategories();
        if (is_array($categories) && count($categories)) {
            foreach ($this->getCategories() as $categoryArray) {
                if (is_object($categoryArray)) {
                    if ($first) {
                        $this->categoryUidsAsArray [] = $categoryArray->getUid();
                        $first = false;
                    } else {
                        $this->categoryUidsAsArray [] = $categoryArray->getUid();
                    }
                }
            }
        }
        return $this->categoryUidsAsArray;
    }
    public function cloneEvent()
    {
        $thisClass = get_class($this);
        $event = new $thisClass($this->getType());
        $event->setIsClone(true);
        return $event;
    }
    
    /**
     * Calls user function defined in TypoScript
     *
     * @param integer $mConfKey
     *        	if this value is empty the var $mConfKey is not processed
     * @param mixed $passVar
     *        	this var is processed in the user function
     * @return mixed processed $passVar
     */
    public function userProcess($mConfKey, $passVar)
    {
        if ($this->conf [$mConfKey]) {
            $funcConf = $this->conf [$mConfKey . '.'];
            $funcConf ['parentObj'] = & $this;
            $passVar = $this->controller->cObj->callUserFunction($this->conf [$mConfKey], $funcConf, $passVar);
        }
        return $passVar;
    }
    public function isExternalPluginEvent()
    {
        return $this->externalPlugin;
    }
    public function getExternalPluginEventLink()
    {
    }
    public function addAdditionalSingleViewUrlParams(&$currentParams)
    {
    }
    public function getLengthInSeconds()
    {
        $eventStart = $this->getStart();
        $eventEnd = $this->getEnd();
        $days = Calc::dateDiff($eventStart->getDay(), $eventStart->getMonth(), $eventStart->getYear(), $eventEnd->getDay(), $eventEnd->getMonth(), $eventEnd->getYear());
        $hours = $eventEnd->getHour() - $eventStart->getHour();
        $minutes = $eventEnd->getMinute() - $eventStart->getMinute();
        return $days * 86400 + $hours * 3600 + $minutes * 60;
    }
    public function addAttendee(&$attendee)
    {
        $this->attendees [$attendee->getType()] [] = $attendee;
    }
    public function setAttendees(&$attendees)
    {
        $this->attendees = &$attendees;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function getPriority()
    {
        return $this->priority;
    }
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }
    public function getCompleted()
    {
        return $this->completed;
    }
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }
    public function getDeviationDates()
    {
        return $this->deviationDates;
    }
    public function setDeviationDates($deviationDates)
    {
        $this->deviationDates = $deviationDates;
    }
}
