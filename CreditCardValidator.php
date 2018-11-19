<?php
/**
 * Inspired from http://www.yiiframework.com/extension/eccvalidator/
 *
 * Migrated Yii 1 to Yii 2 Credit card validator
 *
 * @author Bryan Tan <bryantan16@gmail.com>
 */

namespace bryglen\validators;

use yii\base\InvalidValueException;
use yii\validators\Validator;
use Yii;

class CreditCardValidator extends Validator
{
    /**
     *
     * Detected Credit Card list
     * @var string
     * @link http://en.wikipedia.org/wiki/Bank_card_number#cite_note-NoMoreBankCard-4
     */
    const MAESTRO = 'Maestro';
    const SOLO = 'Solo';
    const VISA = 'Visa';
    const ELECTRON = 'Electron';
    const AMERICAN_EXPRESS = 'American_Express';
    const MASTERCARD = 'Mastercard';
    const DISCOVER = 'Discover';
    const JCB = 'JCB';
    const VOYAGER = 'Voyager';
    const DINERS_CLUB = 'Diners_Club';
    const SWITCH_CARD = 'Switch';
    const LASER = 'Laser';
    const ALL = 'All';

    /**
     *
     * @var array holds the regex patterns to check for valid
     * Credit Card number prefixes
     */
    protected $patterns = array(
        self::MASTERCARD => '/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/',
        self::VISA => '/^4[0-9]{12}([0-9]{3})?$/',
        self::AMERICAN_EXPRESS => '/^3[47][0-9]{13}$/',
        self::DINERS_CLUB => '/^3(0[0-5]|[68][0-9])[0-9]{11}$/',
        self::DISCOVER => '/^(6011\d{12}|65\d{14})$/',
        self::JCB => '/^(3[0-9]{4}|2131|1800)[0-9]{11}$/',
        self::VOYAGER => '/^8699[0-9]{11}$/',
        self::SOLO => '/^(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?$/',
        self::MAESTRO => '/^(?:5020|6\\d{3})\\d{12}$/',
        self::SWITCH_CARD => '/^(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)$/',
        self::ELECTRON => '/^(?:417500|4026\\d{2}|4917\\d{2}|4913\\d{2}|4508\\d{2}|4844\\d{2})\\d{10}$/',
        self::LASER => '/^(?:6304|6706|6771|6709)\\d{12}(\\d{2,3})?$/',
        self::ALL => '/^((?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}|4[0-9]{12}([0-9]{3})?|3[47][0-9]{13}|3(0[0-5]|[68][0-9])[0-9]{11}|(6011\d{12}|65\d{14})|(3[0-9]{4}|2131|1800)[0-9]{11}|2(?:014|149)\\d{11}|8699[0-9]{11}|(6334[5-9][0-9]|6767[0-9]{2})\\d{10}(\\d{2,3})?|(?:5020|6\\d{3})\\d{12}|56(10\\d\\d|022[1-5])\\d{10}|(?:49(03(0[2-9]|3[5-9])|11(0[1-2]|7[4-9]|8[1-2])|36[0-9]{2})\\d{10}(\\d{2,3})?)|(?:564182\\d{10}(\\d{2,3})?)|(6(3(33[0-4][0-9])|759[0-9]{2})\\d{10}(\\d{2,3})?)|(?:417500|4026\\d{2}|4917\\d{2}|4913\\d{2}|4508\\d{2}|4844\\d{2})\\d{10}|(?:417500|4026\\d{2}|4917\\d{2}|4913\\d{2}|4508\\d{2}|4844\\d{2})\\d{10})$/'
    );

    public $messageFormat;

    /**
     *
     * @var string set with selected Credit Card type to check -ie ECCValidator::MAESTRO
     */
    public $format = self::ALL;

    public function init()
    {
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is not a valid Credit Card number.');
        }
        if ($this->messageFormat === null) {
            $this->messageFormat = Yii::t('yii', 'The "format" property must be specified with a supported Credit Card format.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;

        $result = $this->validateValue($object->$attribute);
        if (!empty($result)) {
            $this->addError($object, $attribute, $result[0], $result[1]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!$this->checkType()) {
            return [$this->messageFormat, []];
        }
        $creditCardNumber = preg_replace('/[ -]+/', '', $value);
        $valid = $this->checkFormat($creditCardNumber) && $this->mod10($creditCardNumber);

        return $valid ? null : [$this->message, []];
    }

    /**
     * Validates a Credit Card date
     *
     * @param $creditCardExpiredMonth
     * @param $creditCardExpiredYear
     * @return bool
     */
    public function validateDate($creditCardExpiredMonth, $creditCardExpiredYear)
    {
        $currentMonth = intval(date('n'));
        $currentYear = intval(date('Y'));

        if (is_scalar($creditCardExpiredMonth)) $creditCardExpiredMonth = intval($creditCardExpiredMonth);
        if (is_scalar($creditCardExpiredYear)) $creditCardExpiredYear = intval($creditCardExpiredYear);

        return
        is_integer($creditCardExpiredMonth) && $creditCardExpiredMonth >= 1 && $creditCardExpiredMonth <= 12 &&
        is_integer($creditCardExpiredYear) &&  ($creditCardExpiredYear > $currentYear || ($creditCardExpiredYear==$currentYear && $creditCardExpiredMonth>=$currentMonth) ) &&  $creditCardExpiredYear < $currentYear + 10
        ;
    }
    
    /**
     * Validates the Credit Card expiration date
     * @param $creditCardExpiredMonth
     * @param $creditCardExpiredYear
     * @return bool
     */
    public static function validateExpirationDate($creditCardExpiredMonth, $creditCardExpiredYear)
    {
        $currentMonth = intval(date('n'));
        $currentYear = intval(date('Y'));
        if (is_scalar($creditCardExpiredMonth)) {
            $creditCardExpiredMonth = intval($creditCardExpiredMonth);
        }
        if (is_scalar($creditCardExpiredYear)) {
            $creditCardExpiredYear = intval($creditCardExpiredYear);
        }
        return
            is_integer($creditCardExpiredMonth) &&
            $creditCardExpiredMonth >= 1 &&
            $creditCardExpiredMonth <= 12 &&
            is_integer($creditCardExpiredYear) &&
            (
                $creditCardExpiredYear > $currentYear || (
                    $creditCardExpiredYear == $currentYear && $creditCardExpiredMonth >= $currentMonth)
            ) &&
            $creditCardExpiredYear < $currentYear + 10;
    }

    /**
     * Validates Credit Card holder
     *
     * @param $creditCardHolder
     * @return bool
     */
    public function validateName($creditCardHolder)
    {
        return !empty($creditCardHolder) && preg_match('/^[A-Z ]+$/i', $creditCardHolder);
    }

    /**
     * Validates holder, number, and dates of Credit Card numbers
     *
     * @param $creditCardHolder
     * @param $creditCardNumber
     * @param $creditCardExpiredMonth
     * @param $creditCardExpiredYear
     * @return bool
     */
    public function validateAll($creditCardHolder, $creditCardNumber, $creditCardExpiredMonth, $creditCardExpiredYear)
    {
        return $this->validateName($creditCardHolder) && $this->validateValue(
            $creditCardNumber
        ) && $this->validateDate($creditCardExpiredMonth, $creditCardExpiredYear);
    }

    /**
     * Checks Credit Card Prefixes
     *
     * @access private
     * @param string $cardNumber
     * @return boolean true|false
     */
    protected function checkFormat($cardNumber)
    {
        return preg_match('/^[0-9]+$/', $cardNumber) && preg_match($this->patterns[$this->format], $cardNumber);
    }

    /**
     * Check credit card number by Mod 10 algorithm
     *
     * @param string $cardNumber
     * @return bool
     * @see http://en.wikipedia.org/wiki/Luhn_algorithm#Mod_10.2B5_Variant
     */
    protected function mod10($cardNumber)
    {
        $cardNumber = strrev($cardNumber);
        $numSum = 0;
        for ($i = 0; $i < strlen($cardNumber); $i++) {
            $currentNum = substr($cardNumber, $i, 1);
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }
            $numSum += $currentNum;
        }
        return ($numSum % 10 == 0);
    }

    /**
     *
     * Checks if Credit Card Format is a supported one
     * and builds new pattern format in case user has
     * a mixed match search (mastercard|visa)
     *
     * @return boolean
     */
    protected function checkType()
    {

        if (is_scalar($this->format)) {
            return array_key_exists($this->format, $this->patterns);
        } else if (is_array($this->format)) {
            $pattern = array();
            foreach ($this->format as $f) {
                if (!array_key_exists($f, $this->patterns)) return false;
                $pattern[] = substr($this->patterns[$f], 2, strlen($this->patterns[$f]) - 4);
            }
            $this->format = 'custom';
            $this->patterns[$this->format] = '/^(' . join('|', $pattern) . ')$/';
            return true;
        }
        return false;

    }
} 
