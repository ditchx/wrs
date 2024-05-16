<?php
/**
 * Database Connection Singleton
 *
 */
class DBConnection
{
    private $readConnection;
    private $writeConnection;
    private $activeConnection;
    private static $instance;

    const READ = 'read';
    const WRITE = 'write';

    /**
     * Private Constructor
     *
     * @param PDO $read connection to use for read operations
     * @param PDO $write connection to user for write operations
     */
    private function __construct(PDO $read, PDO $write)
    {
        $this->readConnection = $read;
        $this->writeConnection = $write;
    }

    /**
     *
     * Get singleton instance
     *
     * @param PDO $read connection to use for read operations
     * @param PDO $write connection to user for write operations
     * @return DBConnection
     */
    public static function getInstance(PDO $read, PDO $write)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($read, $write);
        }

        return self::$instance;
    }

    /**
     * Execute SQL query
     *
     * @param string $sql
     * @param mixed $options
     * @return mixed
     */
    public function query($sql, $options = array())
    {
        // Set fetch mode to use for select queries
        $mode = PDO::FETCH_ASSOC;
        if (isset($options['fetch_mode'])) {
            $mode = $options['fetch_mode'];
        }

        // Prepare sql statement
        $statement = $this->activeConnection->prepare($sql);

        // and gather params if provided
        $params = null;
        if (isset($options['params'])) {
            $params = $options['params'];
        }

        // Execute SQL with params (if any)
        $result = $statement->execute($params);

        // Something went wrong with the query
        // throw an error
        if ($result === false) {
            $err = $this->activeConnection->errorInfo();
            throw new Exception('SQL Error: ' . $err[2]);
        }

        // A select query was performed
        // return results
        if (stripos(trim($sql), 'SELECT') === 0) {
            return $statement->fetchAll($mode);
        }

        // An insert query was performed
        // return last inserted id
        if (stripos(trim($sql), 'INSERT') === 0) {
            return $this->activeConnection->lastInsertId();
        }


        return true;
    }

    /**
     * Set active connection type to either READ or WRITE
     *
     * @param string $type
     * @return void
     */
    public function connect($type)
    {
        if ($type === self::READ) {
            $this->activeConnection = $this->readConnection;
        }

        if ($type === self::WRITE) {
            $this->activeConnection = $this->writeConnection;
        }

    }

    private function __clone()
    {

    }

}
