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

/**
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class LocationPartner extends \TYPO3\CMS\Cal\Model\Location
{
    private $partner;
    
    /**
     * Constructor
     *
     * @param integer $uid
     *        	to search for
     * @param string $pidList
     *        	to search in
     */
    public function __construct($uid, $pidList)
    {
        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('partner') . 'api/class.tx_partner_main.php');
        require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('partner') . 'api/class.tx_partner_div.php');
        
        $this->partner = new \tx_partner_main();
        $this->partner->getPartner($uid);
        $this->partner->getContactInfo($this->conf ['view.'] ['location.'] ['contactInfoType']);
        
        $this->Location($this->partner->data, $this->getType());
        
        $this->setType('tx_partner_main');
        $this->setObjectType('location');
        $this->templatePath = $this->conf ['view.'] ['location.'] ['locationModelTemplate4partner'];
    }
    public function getName()
    {
        $partnername = '';
        switch ($this->partner->data ['type']) {
            case 0:
                $partnername = $this->partner->data ['first_name'] . ' ' . $this->partner->data ['last_name'];
                break;
            case 1:
                $partnername = $this->partner->data ['org_name'];
                break;
            default:
                $partnername = $this->partner->data ['label'];
        }
        return $partnername;
    }
    public function getFirstName()
    {
        return $this->partner->data ['first_name'];
    }
    public function setFirstName($t)
    {
        $this->partner->data ['first_name'] = $t;
    }
    public function getMiddleName()
    {
        return $this->partner->data ['middle_name'];
    }
    public function setMiddleName($t)
    {
        $this->partner->data ['middle_name'] = $t;
    }
    public function getLastName()
    {
        return $this->partner->data ['last_name'];
    }
    public function setLastName($t)
    {
        $this->partner->data ['last_name'] = $t;
    }
    public function getStreetNumber()
    {
        return $this->partner->data ['street_number'];
    }
    public function setStreetNumber($t)
    {
        $this->partner->data ['street_number'] = $t;
    }
    public function getZip()
    {
        return $this->partner->data ['postal_code'];
    }
    public function setZip($t)
    {
        $this->partner->data ['postal_code'] = $t;
    }
    public function fillTemplate($subpartMarker)
    {
        $GLOBALS['LANG']->includeLLFile('EXT:partner/locallang.php');
        return parent::fillTemplate($subpartMarker);
    }
}
