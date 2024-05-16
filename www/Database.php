<?php
/**
 * Database class performs basic SQL operations
 * such as SELECT, INSERT, UPDATE and DELETE
 *
 */
class Database
{
    private $connection;

    /**
     * Attach DB connection to use
     *
     * @return void
     */
    public function connect(DBConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Remove/unset DB connection
     *
     * @return void
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * Perform SELECT query
     *
     * @param mixed $table
     * @param mixed $options
     */
    public function select($table, $options = array())
    {
        // List fields to fetch
        $fields = "*";
        if (isset($options['fields'])) {
            $fields = implode(', ', $options['fields']);
        }

        // Start building SQL

        // Base select query
        $sql = "SELECT $fields FROM $table \n";

        // Build JOIN statements if provided
        if (isset($options['joins'])) {
            $joins = array();

            foreach($options['joins'] as $j) {

                // Default to INNER JOIN if no join type specified
                $joinType = isset($j['type']) ? strtoupper($j['type']) : 'INNER';

                // Default to INNER JOIN if join type given is not supported
                if (!in_array($joinType, array('INNER', 'LEFT', 'RIGHT', 'OUTER'))) {
                    $joinType = 'INNER';
                }

                // Combine join conditions
                $joinConditions = implode(' AND ', $j['conditions']);

                // ... and add to the list of join statements
                $joins[] = "$joinType JOIN $j[table] ON $joinConditions";

            }

            // Combine all JOIN statements
            // and add to current SQL
            $sql .= implode("\n", $joins) . " \n";
        }

        // Build where conditions
        $conditions = ' 1 ';
        if (isset($options['conditions'])) {
            $conditions = implode(' AND ', $options['conditions']);
        }
        $sql .= "WHERE \n $conditions";

        // Build GROUP statement if given
        if (isset($options['group'])) {
            $sql .= "\nGROUP BY \n " . implode(', ', $options['group']);
        }

        // Build HAVING statement if given
        if (isset($options['having'])) {
            $sql .= "\nHAVING \n " . implode(' AND ', $options['having']);
        }

        // Build ORDER statement if given
        if (isset($options['order'])) {
            $sql .= "\nORDER BY \n " . implode(', ', $options['order']);
        }


        // User read DBConnection for SELECT
        $this->connection->connect(DBConnection::READ);

        // Include any parameters given
        $queryOptions = array();
        if (isset($options['params'])) {
            $queryOptions['params'] = $options['params'];
        }

        $result = $this->connection->query($sql, $queryOptions);

        return $result;
    }

    /**
     * Perform INSERT query
     *
     * @param mixed $table
     * @param mixed $data
     */
    public function insert($table, $data)
    {
        $fields = array();
        $values = array();
        foreach($data as $key => $val) {
            $fields[] = "`$key`";
            $values[] = ":$key";
        }

        $sql = "INSERT INTO $table ("
            . implode(',', $fields)
            . ") VALUES (". implode(',', $values) .")";

        $this->connection->connect(DBConnection::WRITE);
        return $this->connection->query($sql, array('params' => $data));
    }

    /**
     * Perform DELETE query
     *
     * @param mixed $table
     * @param mixed $options
     */
    public function delete($table, $options)
    {
        $sql = "DELETE FROM $table ";

        // Build WHERE statement
        $conditions = array('1');
        if (isset($options['conditions'])) {
            $conditions = implode(' AND ', $options['conditions']);
        }
        $sql .= "WHERE \n $conditions";

         
        // Include any parameters given
        $queryOptions = array();
        if (isset($options['params'])) {
            $queryOptions['params'] = $options['params'];
        }

        // Use writable DBConnection for delete query
        $this->connection->connect(DBConnection::WRITE);
        $result = $this->connection->query($sql, $queryOptions);

        return $result;

    }

    /**
     * Perform UPDATE query
     *
     * @param mixed $table
     * @param mixed $data
     * @param mixed $options
     */
    public function update($table, $data, $options)
    {
        $sql = "UPDATE $table SET ";

        // We need to first determine which type
        // of parameter/placeholder should be used for query
        // Default to named placeholder (e.g. SET name=:set_name WHERE id = :id)
        $namedPlaceholder = true;
        $conditionParams = array();

        // Check if there are given parameters
        // for condition params
        if (isset($options['params'])) {
            $conditionParams = $options['params'];

            // If the array is just a simple list 
            // instead of an associative array,
            // Set $namedPlaceholder to false to use 
            // positional placeholders (e.g. SET name=? WHERE id=?) 
            if (array_keys($conditionParams) === range(0, count($conditionParams) - 1)) {
                $namedPlaceholder = false;
            }
        }

        // Build query parameters for setting data
        // depending of the placeholder type
        $dataParams = array();
        if ($namedPlaceholder) {
            $updateList = array();
            foreach ($data as $key => $val) {
                $updateList[] = " $key = :set_$key ";
                $dataParams["set_$key"] = $val;
            }
        } else {
            $updateList = array();
            foreach ($data as $key => $val) {
                $updateList[] = " $key =? ";
                $dataParams[] = $val;
            }
        }

        // Build WHERE statement
        $sql .= implode(',', $updateList);

        $conditions = array('1');
        if (isset($options['conditions'])) {
            $conditions = implode(' AND ', $options['conditions']);
        }
        $sql .= "WHERE \n $conditions";

        // Combine params for setting data and where query
        $queryOptions = array(
            'params' => array_merge($dataParams, $conditionParams),
        );

        // Use writable DBConnection for updates
        $this->connection->connect(DBConnection::WRITE);
        return $this->connection->query($sql, $queryOptions);
    }
}
