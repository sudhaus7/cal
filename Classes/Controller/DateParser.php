<?php
namespace TYPO3\CMS\Cal\Controller;

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
 * date parser
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class DateParser
{
    public $tokenString = '';
    public $mode = - 1; // 0 = string, 1 = number, 2 = range
    public $stack = array();
    public $day = 0;
    public $week = 0;
    public $weekday = - 1;
    public $month = 0;
    public $year = 0;
    public $special = '';
    public $timeObj;
    public $conf;
    
    public function parse($value, $conf = array(), $timeObj = '')
    {
        if ($timeObj == '') {
            $timeObj = new \TYPO3\CMS\Cal\Model\CalDate();
            $timeObj->setTZbyId('UTC');
        }
        $this->timeObj = $timeObj;
        $this->conf = &$conf;
        for ($i = 0; $i < strlen($value); $i ++) {
            $chr = $value {$i};
            
            switch ($chr) {
                case ' ':
                case '_':
                case '.':
                case ':':
                case ',':
                case '/':
                    if ($this->tokenString != '') {
                        if ($this->mode == 0) {
                            $this->_parseString($this->tokenString);
                        } else {
                            $this->_parseNumber($this->tokenString);
                        }
                        $this->tokenString = '';
                    }
                    $this->mode = - 1;
                    break;
                case '-':
                case '+':
                    if ($this->mode == - 1) {
                        $this->mode = 2;
                        array_push($this->stack, array(
                                '?',
                                $chr
                        ));
                    } else {
                        $this->_parseString($this->tokenString);
                        $this->tokenString = '';
                        $this->mode = 0;
                    }
                    break;
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                    if ($this->mode == 1) {
                        $firstPart = array_pop($this->stack);
                        $firstPart = array_pop($firstPart);
                        $this->_parseNumber($firstPart . $chr);
                    } elseif ($this->mode == 2) {
                        $firstPart = array_pop($this->stack);
                        $firstPart = array_pop($firstPart);
                        array_push($this->stack, array(
                                'range' => intval($firstPart . $chr)
                        ));
                    } else {
                        $this->_parseNumber($chr);
                    }
                    if ($this->mode != 2) {
                        $this->mode = 1;
                    }
                    $this->tokenString = '';
                    break;
                case 'A':
                case 'B':
                case 'C':
                case 'D':
                case 'E':
                case 'F':
                case 'G':
                case 'H':
                case 'I':
                case 'J':
                case 'K':
                case 'L':
                case 'M':
                case 'N':
                case 'O':
                case 'P':
                case 'Q':
                case 'R':
                case 'S':
                case 'T':
                case 'U':
                case 'V':
                case 'W':
                case 'X':
                case 'Y':
                case 'Z':
                case 'a':
                case 'b':
                case 'c':
                case 'd':
                case 'e':
                case 'f':
                case 'g':
                case 'h':
                case 'i':
                case 'j':
                case 'k':
                case 'l':
                case 'm':
                case 'n':
                case 'o':
                case 'p':
                case 'q':
                case 'r':
                case 's':
                case 't':
                case 'u':
                case 'v':
                case 'w':
                case 'x':
                case 'y':
                case 'z':
                    if ($this->mode == 1) {
                        $this->_parseString($this->tokenString);
                        $this->tokenString = '';
                    }
                    $this->mode = 0;
                    $this->tokenString .= $chr;
                    break;
                default:
                    break;
            }
        }
        
        if ($this->tokenString != '') {
            if ($this->mode == 0) {
                $this->_parseString($this->tokenString);
            } else {
                $this->_parseNumber($this->tokenString);
            }
        }
    }
    public function _parseNumber($num)
    {
        $number = intval($num);
        if ($this->mode != 2) {
            if ($number > 31) {
                array_push($this->stack, array(
                        'year' => $number
                ));
                return;
            }
            if ($number > 12) {
                array_push($this->stack, array(
                        'day' => $number
                ));
                return;
            }
        }
        array_push($this->stack, array(
                '?' => $number
        ));
    }
    public function _parseString($value)
    {
        $value = strtolower($value);
        switch ($value) {
            case 'last':
                array_push($this->stack, array(
                        'range' => 'last'
                ));
                break;
            case 'next':
                array_push($this->stack, array(
                        'range' => 'next'
                ));
                break;
            case 'now':
                array_push($this->stack, array(
                        'abs' => $this->timeObj->getTime()
                ));
                break;
            case 'today':
                array_push($this->stack, array(
                        'today' => $this->timeObj->getTime()
                ));
                break;
            case 'current':
                array_push($this->stack, array(
                        'today' => $this->timeObj->getTime()
                ));
                break;
            case 'tomorrow':
                array_push($this->stack, array(
                        'tomorrow' => $this->timeObj->getTime()
                ));
                break;
            case 'yesterday':
                array_push($this->stack, array(
                        'yesterday' => $this->timeObj->getTime()
                ));
                break;
            
            case 'yearstart':
                array_push($this->stack, array(
                        'date' => \TYPO3\CMS\Cal\Controller\Calendar::calculateStartYearTime($this->timeObj)
                ));
                break;
            case 'monthstart':
                array_push($this->stack, array(
                        'date' => \TYPO3\CMS\Cal\Controller\Calendar::calculateStartMonthTime($this->timeObj)
                ));
                break;
            case 'weekstart':
                array_push($this->stack, array(
                        'date' => \TYPO3\CMS\Cal\Controller\Calendar::calculateStartWeekTime($this->timeObj)
                ));
                break;
            case 'weekend':
                array_push($this->stack, array(
                        'date' => \TYPO3\CMS\Cal\Controller\Calendar::calculateEndWeekTime($this->timeObj)
                ));
                break;
            case 'monthend':
                array_push($this->stack, array(
                        'date' => \TYPO3\CMS\Cal\Controller\Calendar::calculateEndMonthTime($this->timeObj)
                ));
                break;
            case 'yearend':
                array_push($this->stack, array(
                        'date' => \TYPO3\CMS\Cal\Controller\Calendar::calculateEndYearTime($this->timeObj)
                ));
                break;
            case 'quarterstart':
                $timeObj = $this->timeObj;
                $startMonth = '01';
                switch ($timeObj->getQuarterOfYear()) {
                    case 2:
                        $startMonth = '04';
                        break;
                    case 3:
                        $startMonth = '07';
                        break;
                    case 4:
                        $startMonth = '10';
                        break;
                }
                $timeObj->setDay(1);
                $timeObj->setMonth($startMonth);
                $timeObj->setHour(0);
                $timeObj->setMinute(0);
                $timeObj->setSecond(0);
                array_push($this->stack, array(
                        'date' => $timeObj
                ));
                break;
            case 'quarterend':
                $timeObj = $this->timeObj;
                $endDay = '31';
                $endMonth = '03';
                switch ($timeObj->getQuarterOfYear()) {
                    case 2:
                        $endDay = '30';
                        $endMonth = '06';
                        break;
                    case 3:
                        $endDay = '30';
                        $endMonth = '09';
                        break;
                    case 4:
                        $endDay = '31';
                        $endMonth = '12';
                        break;
                }
                $timeObj->setDay($endDay);
                $timeObj->setMonth($endMonth);
                $timeObj->setHour(23);
                $timeObj->setMinute(59);
                $timeObj->setSecond(59);
                array_push($this->stack, array(
                        'date' => $timeObj
                ));
                break;
            case 'day':
            case 'days':
                array_push($this->stack, array(
                        'value' => 86400
                ));
                break;
            case 'week':
            case 'weeks':
                array_push($this->stack, array(
                        'value' => 604800
                ));
                break;
            case 'h':
            case 'hour':
            case 'hours':
                $value = array_pop(array_pop($this->stack));
                array_push($this->stack, array(
                        'range' => $value
                ));
                array_push($this->stack, array(
                        'value' => 'hour'
                ));
                break;
            case 'm':
            case 'minute':
            case 'minutes':
                $value = array_pop(array_pop($this->stack));
                array_push($this->stack, array(
                        'range' => $value
                ));
                array_push($this->stack, array(
                        'value' => 'minute'
                ));
                break;
            case 'month':
            case 'months':
                array_push($this->stack, array(
                        'value' => 'month'
                ));
                break;
            case 'year':
            case 'years':
                array_push($this->stack, array(
                        'value' => 'year'
                ));
                break;
            case 'mon':
            case 'monday':
                array_push($this->stack, array(
                        'weekday' => 1
                ));
                break;
            case 'tue':
            case 'tuesday':
                array_push($this->stack, array(
                        'weekday' => 2
                ));
                break;
            case 'wed':
            case 'wednesday':
                array_push($this->stack, array(
                        'weekday' => 3
                ));
                break;
            case 'thu':
            case 'thursday':
                array_push($this->stack, array(
                        'weekday' => 4
                ));
                break;
            case 'fri':
            case 'friday':
                array_push($this->stack, array(
                        'weekday' => 5
                ));
                break;
            case 'sat':
            case 'saturday':
                array_push($this->stack, array(
                        'weekday' => 6
                ));
                break;
            case 'sun':
            case 'sunday':
                array_push($this->stack, array(
                        'weekday' => 0
                ));
                break;
            case 'jan':
            case 'january':
                array_push($this->stack, array(
                        'month' => 1
                ));
                break;
            case 'feb':
            case 'february':
                array_push($this->stack, array(
                        'month' => 2
                ));
                break;
            case 'mar':
            case 'march':
                array_push($this->stack, array(
                        'month' => 3
                ));
                break;
            case 'apr':
            case 'april':
                array_push($this->stack, array(
                        'month' => 4
                ));
                break;
            case 'may':
                array_push($this->stack, array(
                        'month' => 5
                ));
                break;
            case 'jun':
            case 'june':
                array_push($this->stack, array(
                        'month' => 6
                ));
                break;
            case 'jul':
            case 'july':
                array_push($this->stack, array(
                        'month' => 7
                ));
                break;
            case 'aug':
            case 'august':
                array_push($this->stack, array(
                        'month' => 8
                ));
                break;
            case 'sep':
            case 'september':
                array_push($this->stack, array(
                        'month' => 9
                ));
                break;
            case 'oct':
            case 'october':
                array_push($this->stack, array(
                        'month' => 10
                ));
                break;
            case 'nov':
            case 'november':
                array_push($this->stack, array(
                        'month' => 11
                ));
                break;
            case 'dec':
            case 'december':
                array_push($this->stack, array(
                        'month' => 12
                ));
                break;
            default:
                break;
        }
    }
    public function getDateObjectFromStack()
    {
        $date = new \TYPO3\CMS\Cal\Model\CalDate();
        $date->setTZbyId('UTC');
        $date->copy($this->timeObj);
        $lastKey = '';
        $post = array();
        $foundMonth = false;
        $range = '';
        $rangeValue = '';
        while (! empty($this->stack)) {
            $valueArray = array_shift($this->stack);
            foreach ($valueArray as $key => $value) {
                switch ($key) {
                    case 'year':
                        if (strlen($value) == 8) {
                            $date->setYear(intval(substr($value, 0, 4)));
                            $date->setMonth(intval(substr($value, 4, 2)));
                            $date->setDay(intval(substr($value, 6, 2)));
                        } else {
                            $date->setYear($value);
                        }
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        break;
                    case 'month':
                        $date->setMonth($value);
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        $foundMonth = true;
                        break;
                    case 'day':
                        $date->setDay($value);
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        break;
                    case 'week':
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        $date->addSeconds($value);
                        break;
                    case 'hour':
                        $date->setDay(0);
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour($value);
                        break;
                    case 'minute':
                        $date->setDay(0);
                        $date->setMinute($value);
                        $date->setSecond(0);
                        $date->setHour(0);
                        break;
                    case '?':
                        if ($lastKey == 'month') {
                            $date->setDay($value);
                            $date->setMinute(0);
                            $date->setSecond(0);
                            $date->setHour(0);
                            $key = 'day';
                        } elseif ($lastKey == 'year') {
                            if ($this->conf ['USmode']) {
                                $date->setDay($value);
                                $date->setMinute(0);
                                $date->setSecond(0);
                                $date->setHour(0);
                                $key = 'day';
                            } else {
                                $date->setMonth($value);
                                $date->setMinute(0);
                                $date->setSecond(0);
                                $date->setHour(0);
                                $foundMonth = true;
                                $key = 'month';
                            }
                        } elseif ($lastKey == 'day') {
                            $date->setMonth($value);
                            $date->setMinute(0);
                            $date->setSecond(0);
                            $date->setHour(0);
                            $foundMonth = true;
                            $key = 'month';
                        } else {
                            $post [] = $valueArray;
                        }
                        break;
                    case 'range':
                        $range = $value;
                        if ($rangeValue) {
                            $this->evaluateRange($date, $range, $rangeValue);
                            // after parsing the rangeValue, clear it so that a new range can start
                            $range = false;
                        }
                        break;
                    case 'value':
                    case 'weekday':
                        $rangeValue = $value;
                        if ($range) {
                            $this->evaluateRange($date, $range, $rangeValue);
                            // after parsing the range, clear it so that a new range can start
                            $rangeValue = false;
                        }
                        break;
                    case 'today':
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        break;
                    case 'tomorrow':
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        $date->addSeconds(86400);
                        break;
                    case 'yesterday':
                        $date->setMinute(0);
                        $date->setSecond(0);
                        $date->setHour(0);
                        $date->subtractSeconds(86400);
                        break;
                    case 'date':
                        $date->copy($value);
                        // no break
                    default:
                        $post [] = $valueArray;
                        break;
                }
                $lastKey = $key;
            }
        }
        
        while (! empty($post)) {
            $valueArray = array_pop($post);
            foreach ($valueArray as $key => $value) {
                switch ($key) {
                    case '?':
                        if ($foundMonth) {
                            $date->setDay($value);
                        } else {
                            if ($this->conf ['USmode']) {
                                $date->setDay($value);
                            } else {
                                $date->setMonth($value);
                                $foundMonth = true;
                            }
                        }
                        break;
                }
            }
        }
        return $date;
    }
    public function evaluateRange(&$date, $range, $rangeValue)
    {
        if (! is_numeric($range)) {
            if ($range == 'last') {
                $range = - 1;
            } elseif ($range == 'next') {
                $range = 1;
            }
        }
        if (is_numeric($rangeValue)) {
            $date->addSeconds($rangeValue * $range);
        } elseif (is_array($rangeValue)) {
            foreach ($rangeValue as $key => $value) {
                if ($key == 'weekday' && $range > 0) {
                    for ($i = 0; $i < $range; $i ++) {
                        $formatedDate = Calc::nextDayOfWeek($value, $date->getDay(), $date->getMonth(), $date->getYear());
                        $date = new \TYPO3\CMS\Cal\Model\CalDate($formatedDate);
                        $date->setTZbyId('UTC');
                    }
                } elseif ($key == 'weekday' && $range < 0) {
                    for ($i = 0; $i > $range; $i --) {
                        $formatedDate = Calc::prevDayOfWeek($value, $date->getDay(), $date->getMonth(), $date->getYear());
                        $date = new \TYPO3\CMS\Cal\Model\CalDate($formatedDate);
                        $date->setTZbyId('UTC');
                    }
                } elseif ($value == 'week' && $range > 0) {
                    $date->addSeconds($range * 604800);
                } elseif ($value == 'week' && $range < 0) {
                    $date->subtractSeconds($range * 604800);
                }
            }
        } elseif ($range > 0) {
            if ($rangeValue == 'month') {
                for ($i = 0; $i < $range; $i ++) {
                    $days = Calc::daysInMonth($date->getMonth(), $date->getYear());
                    $endOfNextMonth = new \TYPO3\CMS\Cal\Model\CalDate(Calc::endOfNextMonth($date->getDay(), $date->getMonth(), $date->getYear()));
                    $date->addSeconds(60 * 60 * 24 * $days);
                    if ($date->after($endOfNextMonth)) {
                        $date->setDay($endOfNextMonth->getDay());
                        $date->setMonth($endOfNextMonth->getMonth());
                        $date->setYear($endOfNextMonth->getYear());
                    }
                }
            } elseif ($rangeValue == 'year') {
                $date->setYear($date->getYear() + $range);
            } elseif ($rangeValue == 'hour') {
                $date->addSeconds($range * 3600);
            } elseif ($rangeValue == 'minute') {
                $date->addSeconds($range * 60);
            } else {
                $date->addSeconds($range * 86400);
            }
        } elseif ($range < 0) {
            if ($rangeValue == 'month') {
                for ($i = 0; $i > $range; $i --) {
                    $endOfPrevMonth = new \TYPO3\CMS\Cal\Model\CalDate(Calc::endOfPrevMonth($date->getDay(), $date->getMonth(), $date->getYear()));
                    $days = Calc::daysInMonth($endOfPrevMonth->getMonth(), $endOfPrevMonth->getYear());
                    $date->subtractSeconds(60 * 60 * 24 * $days);
                }
            } elseif ($rangeValue == 'year') {
                $date->setYear($date->getYear() - abs($range));
            } elseif ($rangeValue == 'hour') {
                $date->subtractSeconds(abs($range) * 3600);
            } elseif ($rangeValue == 'minute') {
                $date->subtractSeconds(abs($range) * 60);
            } else {
                $date->subtractSeconds(abs($range) * 86400);
            }
        }
        $date->subtractSeconds(1);
    }
}
