<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\models\Order as ModelsOrder;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to payment transactions
 */
class Transaction extends Model
{

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsOrder $order
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsOrder $order, ModelsLanguage $language)
    {
        parent::__construct();

        $this->order = $order;
        $this->language = $language;
    }

    /**
     * Returns an array of transactions
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT t.*';

        if (!empty($data['count'])) {
            $sql = ' SELECT COUNT(t.transaction_id) ';
        }

        $sql .= ' FROM transaction t';

        $where = array();

        if (isset($data['order_id'])) {
            $sql .= ' AND t.order_id = ?';
            $where[] = (int) $data['order_id'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND t.created = ?';
            $where[] = (int) $data['created'];
        }

        if (isset($data['payment_method'])) {
            $sql .= ' AND t.payment_method LIKE ?';
            $where[] = "%{$data['payment_method']}%";
        }

        if (isset($data['remote_transaction_id'])) {
            $sql .= ' AND t.remote_transaction_id LIKE ?';
            $where[] = "%{$data['remote_transaction_id']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('order_id', 'created', 'payment_method', 'remote_transaction_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort)) && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY t.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY t.created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'transaction_id', 'unserialize' => 'data');
        $results = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('transaction.list', $results);
        return $results;
    }

    /**
     * Adds a transaction
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.transaction.before', $data);

        if (empty($data)) {
            return false;
        }

        $data += array('created' => GC_TIME);
        $data['transaction_id'] = $this->db->insert('transaction', $data);

        $this->hook->fire('add.transaction.after', $data);

        return $data['transaction_id'];
    }

    /**
     * Loads a transaction the database
     * @param integer $transaction_id
     * @return array
     */
    public function get($transaction_id)
    {
        $sql = 'SELECT * FROM transaction WHERE transaction_id=?';

        $options = array('unserialize' => 'data');
        return $this->db->fetch($sql, array($transaction_id), $options);
    }

    /**
     * Deletes a transaction
     * @param integer $transaction_id
     * @return boolean
     */
    public function delete($transaction_id)
    {
        $this->hook->fire('delete.transaction.before', $transaction_id);

        if (empty($transaction_id)) {
            return false;
        }

        $conditions = array('transaction_id' => $transaction_id);
        $result = $this->db->delete('transaction', $conditions);

        $this->hook->fire('delete.transaction.after', $transaction_id, $result);
        return (bool) $result;
    }

    /**
     * Updates a transaction
     * @param integer $transaction_id
     * @param array $data
     * @return boolean
     */
    public function update($transaction_id, array $data)
    {
        $this->hook->fire('update.transaction.before', $transaction_id, $data);

        if (empty($transaction_id)) {
            return false;
        }

        $conditions = array('transaction_id' => $transaction_id);
        $result = $this->db->update('transaction', $data, $conditions);

        $this->hook->fire('update.transaction.after', $transaction_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Processes a remote transaction for the given order ID
     * @param integer $order_id
     * @param array $request
     */
    public function remote($order_id, array $request = array())
    {
        $order = $this->order->get($order_id);

        $error = array(
            'redirect' => '/',
            'severity' => 'danger',
            'message' => $this->language->text('An error occurred')
        );

        if (empty($order['status'])) {
            return $error;
        }

        $result = array('redirect' => '/', 'message' => '', 'severity' => '');

        $this->hook->fire('remote.transaction', $order, $request, $result);

        if (empty($result['remote_transaction_id'])) {
            return $error;
        }

        $transaction = array(
            'data' => $request,
            'order_id' => $order_id,
            'total' => $order['total'],
            'currency' => $order['currency'],
            'payment_method' => $order['payment'],
            'remote_transaction_id' => $result['remote_transaction_id']
        );

        $transaction_id = $this->add($transaction);

        if (empty($transaction_id)) {
            return $error;
        }
        
        $this->order->update($order_id, array('transaction_id' => $transaction_id));
        return $result;
    }

}
