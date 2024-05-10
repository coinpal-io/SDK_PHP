<?php

namespace coinpal;

class Database
{
    // Le Brand REAL IT Solutions
    protected $connection;
    protected $error;

    // Le Brand REAL IT Solutions
    public function __construct()
    {
        if ( session_status() != PHP_SESSION_ACTIVE ) {
            session_start();
        }
        // Check if session variable exists
        if (!isset($_SESSION['coinpal_installed']) || !$_SESSION['coinpal_installed']) {
            // Check if database credentials exist
            if ($this->checkDBCredentialsExistence()) {
                // Establish database connection
                $this->connect();
                // Check if the coinpal_payments table exists in the database
                $this->checkCoinpalTable();
            }
        }
    }

    // Le Brand REAL IT Solutions
    private function checkDBCredentialsExistence()
    {
        // Check if database credentials variables exist and are not empty

        global $config; // Accessing the $config array from file config.php
//        print_r($config);
        return isset($config['db_host']) && isset($config['db_name']) && isset($config['db_user']) && isset($config['db_pass']) &&
            !empty($config['db_host']) && !empty($config['db_name']) && !empty($config['db_user']) && !empty($config['db_pass']);
    }

    // Le Brand REAL IT Solutions
    public function connect()
    {
        global $config; // Accessing the $config array from file config.php
        $this->connection = mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
        if (!$this->connection) {
            $this->error = 'Connection failed: ' . mysqli_connect_error();
            throw new \Exception("Error executing query: " .  $this->error);
        }
    }

    // Le Brand REAL IT Solutions
    private function checkCoinpalTable()
    {
        // Check if the coinpal_payments table exists in the database

        // Prepare the query to check for the existence of the table
        $query = "SHOW TABLES LIKE 'coinpal_payments'";
        // Execute the query
        $result =  $this->connection->query($query);
        // Check if the query was successful
        if ($result === false) {
            // Handle query error
            throw new \Exception("Error executing query: " .  $this->connection->error);
        }

        // Check if the table exists
        if ($result->num_rows > 0) {
            // Table exists
            $_SESSION['coinpal_installed'] = true;
        } else {
            // Table does not exist, create it along with another table
            $this->createCoinpalTables();
        }
    }

    // Le Brand REAL IT Solutions
    private function createCoinpalTables()
    {
        // Create the coinpal_payments and coinpal_payments_history tables

        // SQL to create the coinpal_payments table
        $createPaymentsTableSQL = "CREATE TABLE `coinpal_payments` (
  `cpid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `version` char(1) NOT NULL DEFAULT '',
  `requestId` varchar(64) NOT NULL DEFAULT '',
  `merchantNo` varchar(32) NOT NULL DEFAULT '',
  `orderNo` varchar(32) NOT NULL DEFAULT '',
  `reference` varchar(32) NOT NULL DEFAULT '',
  `orderCurrency` char(10) NOT NULL DEFAULT '',
  `orderAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `paidOrderAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `dueCurrency` char(10) NOT NULL DEFAULT '',
  `dueAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `paidAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `paidCurrency` char(10) NOT NULL DEFAULT '',
  `paidUsdt` float(10,8) NOT NULL DEFAULT '0.00000000',
  `selectedWallet` varchar(32) NOT NULL DEFAULT '',
  `network` varchar(32) NOT NULL DEFAULT '',
  `confirmedTime` varchar(32) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `sign` varchar(128) NOT NULL DEFAULT '',
  `nextStep` varchar(12) NOT NULL DEFAULT '',
  `nextStepContent` varchar(128) NOT NULL DEFAULT '',
  `respCode` char(3) NOT NULL DEFAULT '',
  `respMessage` varchar(12) NOT NULL DEFAULT '',
  `paidAddress` varchar(255) NOT NULL DEFAULT '',
  `unresolvedLabel` char(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`cpid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        // SQL to create the coinpal_payments_history table
        $createHistoryTableSQL = "CREATE TABLE `coinpal_payments_history` (
  `cphid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `version` char(1) NOT NULL DEFAULT '',
  `requestId` varchar(64) NOT NULL DEFAULT '',
  `merchantNo` varchar(32) NOT NULL DEFAULT '',
  `orderNo` varchar(32) NOT NULL DEFAULT '',
  `reference` varchar(32) NOT NULL DEFAULT '',
  `orderCurrency` char(10) NOT NULL DEFAULT '',
  `orderAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `paidOrderAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `dueCurrency` char(10) NOT NULL DEFAULT '',
  `dueAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `paidAmount` float(10,8) NOT NULL DEFAULT '0.00000000',
  `paidCurrency` char(10) NOT NULL DEFAULT '',
  `paidUsdt` float(10,8) NOT NULL DEFAULT '0.00000000',
  `selectedWallet` varchar(32) NOT NULL DEFAULT '',
  `network` varchar(32) NOT NULL DEFAULT '',
  `confirmedTime` varchar(32) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `sign` varchar(128) NOT NULL DEFAULT '',
  `nextStep` varchar(12) NOT NULL DEFAULT '',
  `nextStepContent` varchar(128) NOT NULL DEFAULT '',
  `respCode` char(3) NOT NULL DEFAULT '',
  `respMessage` varchar(12) NOT NULL DEFAULT '',
  `paidAddress` varchar(255) NOT NULL DEFAULT '',
  `unresolvedLabel` char(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`cphid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        // Execute the SQL queries to create the tables
        $this->connection->query($createPaymentsTableSQL);
        $this->connection->query($createHistoryTableSQL);

        // Set environment flag
        $_SESSION['coinpal_installed'] = true;
    }

    // Execute SQL
    public function execSql($sql = '') {
        if (empty($sql)) {
            return false;
        }
        if (!$this->connection) {
           $this->connect();
        }
        $this->connection->query($sql);
        return true;
    }

    // Assemble SQL
    public function generateInsertSql($table='', $data = []) {
        if (empty($data) || empty($table)) {
            return '';
        }
        $columns = [];
        $values = [];
        foreach ($data as $key => $value) {
            $columns[] = "`$key`";
            $values[] = "'" . $value . "'";
        }
        $sql = "INSERT INTO {$table} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";
        return $sql;
    }


}