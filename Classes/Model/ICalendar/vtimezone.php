<?php
namespace TYPO3\CMS\Cal\Model\ICalendar;

/**
 * Class representing vTimezones.
 *
 * $Horde: framework/iCalendar/iCalendar/vtimezone.php,v 1.8.10.4 2006/01/01 21:28:47 jan Exp $
 *
 * Copyright 2003-2006 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Mike Cochrane <mike@graftonhall.co.nz>
 * @since Horde 3.0
 * @package Horde_iCalendar
 */
class vtimezone extends \TYPO3\CMS\Cal\Model\ICalendar
{
    public function getType()
    {
        return 'vTimeZone';
    }
    public function parsevCalendar($data, $base = 'VCALENDAR', $charset = 'utf8', $clear = true)
    {
        parent::parsevCalendar($data, 'VTIMEZONE');
    }
    public function exportvCalendar()
    {
        return parent::_exportvData('VTIMEZONE');
    }
    
    /**
     * Parse child components of the vTimezone component.
     * Returns an
     * array with the exact time of the time change as well as the
     * 'from' and 'to' offsets around the change. Time is arbitrarily
     * based on UTC for comparison.
     */
    public function parseChild(&$child, $year)
    {
        // Make sure 'time' key is first for sort().
        $result = array();
        $result ['time'] = 0;
        
        $t = $child->getAttribute('TZOFFSETFROM');
        if (empty($t)) {
            return false;
        }
        $result ['from'] = ($t ['hour'] * 60 * 60 + $t ['minute'] * 60) * ($t ['ahead'] ? 1 : - 1);
        
        $t = $child->getAttribute('TZOFFSETTO');
        if (empty($t)) {
            return false;
        }
        $result ['to'] = ($t ['hour'] * 60 * 60 + $t ['minute'] * 60) * ($t ['ahead'] ? 1 : - 1);
        
        $switch_time = $child->getAttribute('DTSTART');
        if (empty($switch_time)) {
            return false;
        }
        
        $rrules = $child->getAttribute('RRULE');
        if (empty($rrules)) {
            if (! is_int($switch_time)) {
                return false;
            }
            // Convert this timestamp from local time to UTC for
            // comparison (All dates are compared as if they are UTC).
            $t = getdate($switch_time);
            $result ['time'] = @gmmktime($t ['hours'], $t ['minutes'], $t ['seconds'], $t ['mon'], $t ['mday'], $t ['year']);
            return $result;
        }
        
        $rrules = explode(';', $rrules);
        foreach ($rrules as $rrule) {
            $t = explode('=', $rrule);
            switch ($t [0]) {
                case 'FREQ':
                    if ($t [1] != 'YEARLY') {
                        return false;
                    }
                    break;
                
                case 'INTERVAL':
                    if ($t [1] != '1') {
                        return false;
                    }
                    break;
                
                case 'BYMONTH':
                    $month = intval($t [1]);
                    break;
                
                case 'BYDAY':
                    $len = strspn($t [1], '1234567890-+');
                    if ($len == 0) {
                        return false;
                    }
                    $weekday = substr($t [1], $len);
                    $weekdays = array(
                            'SU' => 0,
                            'MO' => 1,
                            'TU' => 2,
                            'WE' => 3,
                            'TH' => 4,
                            'FR' => 5,
                            'SA' => 6
                    );
                    $weekday = $weekdays [$weekday];
                    $which = intval(substr($t [1], 0, $len));
                    break;
                
                case 'UNTIL':
                    if (intval($year) > intval(substr($t [1], 0, 4))) {
                        return false;
                    }
                    break;
            }
        }
        
        if (empty($month) || ! isset($weekday)) {
            return false;
        }
        
        if (is_int($switch_time)) {
            // Was stored as localtime.
            $switch_time = strftime('%H:%M:%S', $switch_time);
            $switch_time = explode(':', $switch_time);
        } else {
            $switch_time = explode('T', $switch_time);
            if (count($switch_time) != 2) {
                return false;
            }
            $switch_time [0] = substr($switch_time [1], 0, 2);
            $switch_time [2] = substr($switch_time [1], 4, 2);
            $switch_time [1] = substr($switch_time [1], 2, 2);
        }
        
        // Get the timestamp for the first day of $month.
        $when = gmmktime($switch_time [0], $switch_time [1], $switch_time [2], $month, 1, $year);
        // Get the day of the week for the first day of $month.
        $first_of_month_weekday = intval(gmstrftime('%w', $when));
        
        // Go to the first $weekday before first day of $month.
        if ($weekday >= $first_of_month_weekday) {
            $weekday -= 7;
        }
        $when -= ($first_of_month_weekday - $weekday) * 60 * 60 * 24;
        
        // If going backwards go to the first $weekday after last day
        // of $month.
        if ($which < 0) {
            do {
                $when += 60 * 60 * 24 * 7;
            } while (intval(gmstrftime('%m', $when)) == $month);
        }
        
        // Calculate $weekday number $which.
        $when += $which * 60 * 60 * 24 * 7;
        
        $result ['time'] = $when;
        
        return $result;
    }
}
