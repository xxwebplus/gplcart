<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Zone as ZoneModel;
use core\models\City as CityModel;
use core\models\State as StateModel;
use core\models\Country as CountryModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate city data
 */
class City extends BaseValidator
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Zone model instance
     * @var \core\models\Zone $zone
     */
    protected $zone;

    /**
     * City model instance
     * @var \core\models\City $city
     */
    protected $city;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Constructor
     * @param CityModel $city
     * @param StateModel $state
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(CityModel $city, StateModel $state,
            CountryModel $country, ZoneModel $zone)
    {
        parent::__construct();

        $this->city = $city;
        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Performs full city data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function city(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateCity($options);
        $this->validateStatus($options);
        $this->validateName($options);
        $this->validateStateCity($options);
        $this->validateZoneCity($options);
        $this->validateCountryCity($options);

        return $this->getResult();
    }

    /**
     * Validates a city to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateCity(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->city->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('City'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a state ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateStateCity(array $options)
    {
        $state_id = $this->getSubmitted('state_id', $options);

        if ($this->isUpdating() && !isset($state_id)) {
            return null;
        }

        if (empty($state_id)) {
            $vars = array('@field' => $this->language->text('State'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('state_id', $error, $options);
            return false;
        }

        if (!is_numeric($state_id)) {
            $vars = array('@field' => $this->language->text('State'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('state_id', $error, $options);
            return false;
        }

        $state = $this->state->get($state_id);

        if (empty($state['state_id'])) {
            $vars = array('@name' => $this->language->text('State'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('state_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a zone ID
     * @param array $options
     * @return boolean
     */
    protected function validateZoneCity(array $options)
    {
        $zone_id = $this->getSubmitted('zone_id', $options);

        if (empty($zone_id)) {
            return true;
        }

        if (!is_numeric($zone_id)) {
            $vars = array('@field' => $this->language->text('Zone'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('zone_id', $error, $options);
            return false;
        }

        $zone = $this->zone->get($zone_id);

        if (empty($zone['zone_id'])) {
            $vars = array('@name' => $this->language->text('Zone'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('zone_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a country code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCountryCity(array $options)
    {
        $code = $this->getSubmitted('country', $options);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Country'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('country', $error, $options);
            return false;
        }

        $country = $this->country->get($code);

        if (empty($country['code'])) {
            $vars = array('@name' => $this->language->text('Country'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('country', $error, $options);
            return false;
        }

        return true;
    }

}
