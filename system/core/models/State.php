<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;

/**
 * Manages basic behaviors and data related to country states
 */
class State extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a country state
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('state.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('state', $data);

        $this->hook->attach('state.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Loads a country state from the database
     * @param integer $state_id
     * @return array
     */
    public function get($state_id)
    {
        $result = &gplcart_static(__METHOD__ . "$state_id");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('state.get.before', $state_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM state WHERE state_id=?';
        $result = $this->db->fetch($sql, array($state_id));

        $this->hook->attach('state.get.after', $state_id, $result, $this);
        return $result;
    }

    /**
     * Loads a country state by code
     * @param string $code
     * @param string|null $country
     * @return array
     */
    public function getByCode($code, $country = null)
    {
        $state = $this->getList(array('code' => $code, 'country' => $country));
        return $state ? reset($state) : array();
    }

    /**
     * Returns an array of country states or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(state_id)';
        }

        $sql .= ' FROM state WHERE state_id > 0';
        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['country'])) {
            $sql .= ' AND country = ?';
            $where[] = $data['country'];
        }

        if (isset($data['code'])) {
            $sql .= ' AND code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('country', 'name', 'code', 'status', 'state_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY name ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $states = $this->db->fetchAll($sql, $where, array('index' => 'state_id'));
        $this->hook->attach('state.list', $states, $this);
        return $states;
    }

    /**
     * Deletes a country state
     * @param integer $state_id
     * @return boolean
     */
    public function delete($state_id)
    {
        $result = null;
        $this->hook->attach('state.delete.before', $state_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!$this->canDelete($state_id)) {
            return false;
        }

        $conditions = array('state_id' => $state_id);
        $result = (bool) $this->db->delete('state', $conditions);

        if ($result) {
            $this->db->delete('city', $conditions);
        }

        $this->hook->attach('state.delete.after', $state_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the country state can be deleted
     * @param integer $state_id
     * @return boolean
     */
    public function canDelete($state_id)
    {
        $sql = 'SELECT address_id FROM address WHERE state_id=?';
        $result = $this->db->fetchColumn($sql, array($state_id));

        return empty($result);
    }

    /**
     * Updates a country state
     * @param integer $state_id
     * @param array $data
     * @return boolean
     */
    public function update($state_id, array $data)
    {
        $result = null;
        $this->hook->attach('state.update.before', $state_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('state', $data, array('state_id' => $state_id));

        $this->hook->attach('state.update.after', $state_id, $data, $result, $this);
        return (bool) $result;
    }

}
