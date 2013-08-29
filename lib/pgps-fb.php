<?php

class PersonWithPronouns {
    var $id; // Their Facebook $user_id.
    var $storage_type;
    var $db_connection;

    const FS_PATH = 'people/'; // Used for filesystem storage, if active.
    const PPL_TBL = 'people';  // Table name.
    const PPL_DEF =<<<TABLE_DEFINITION
CREATE TABLE people (
  id                  bigint PRIMARY KEY,
  gender              text,
  personal_subjective varchar(255),
  personal_objective  varchar(255),
  possesive           varchar(255),
  reflexive           varchar(255)
);
TABLE_DEFINITION;

    function PersonWithPronouns ($id) {
        if ( ! (int) $id ) { throw new Exception('Invalid User ID.'); }
        $this->id = $id;
        switch (AppInfo::findBestStorage()) {
            case 'postgres':
            case 'postgresql':
                $this->setStorageType('postgresql');
                break;
            case 'filesystem':
            default:
                $this->setStorageType('filesystem');
                break;
        }
        $this->loadData();
        return $this;
    }

    public function setStorageType ($type) {
        switch ($type) {
            case 'postgres':
            case 'postgresql':
            case 'filesystem':
                $this->storage_type = $type;
                break;
            default:
                throw new Exception("Invalid storage type '$type' passed to " . __CLASS__ . '::' . __METHOD__);
                return false;
        }
        return true;
    }

    private function loadData () {
        switch ($this->storage_type) {
            case 'postgresql':
                $data = $this->loadFromPostgreSQL();
                break;
            case 'filesystem':
            default:
                $data = $this->loadFromFile(self::FS_PATH . $id);
                break;
        }
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    private function loadFromPostgreSQL () {
        $data = array();
        $conn = pg_connect($this->psqlConnectionStringFromDatabaseUrl()) or die('Could not connect to PostrgreSQL server.');
        $this->db_connection = $conn;
        $sql = 'SELECT * FROM ' . pg_escape_identifier(self::PPL_TBL) . ' WHERE id=' . pg_escape_string($this->id);
        $result = pg_query($conn, $sql);
        if (!$result && (false !== strpos(pg_last_error(), 'relation "' . self::PPL_TBL . '" does not exist'))) {
            // TODO: Write a warning log that we're creating the table from scratch ourselves.
            if (!pg_query(self::PPL_DEF)) {
                die('Failed to create table "' . self::PPL_TBL . '", aborting.');
            }
        } else if (!pg_num_rows($result)) {
            // If we don't have a record, we should make one for ourselves.
            $sql = 'INSERT INTO ' . pg_escape_identifier(self::PPL_TBL) . ' (id) VALUES ($1);';
            $result = pg_query_params($conn, $sql, array(
                pg_escape_string($this->id),
            ));
            if (!$result) {
                throw new Exception("Failed to INSERT INTO PostgreSQL database for user with ID {$this->id}.");
            }
        } else {
            $data = pg_fetch_assoc($result);
        }
        return $data;
    }

    private function loadFromFile ($path) {
        if (!file_exists($path)) {
            if (!touch($path)) {
                exit('Cannot create file ' . $path);
            }
        } else {
            ini_set('include_path', get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/pear');
            require_once 'Config/Lite.php';
            return new Config_Lite($path);
        }
    }

    private function psqlConnectionStringFromDatabaseUrl () {
        extract(parse_url(AppInfo::databaseURL()));
        return "user=$user password=$pass host=$host dbname=" . substr($path, 1) . ' sslmode=require';
    }

    public function persist () {
        switch ($this->storage_type) {
            case 'postgres':
            case 'postgresql':
                return $this->writeToPostgreSQL();
            case 'filesystem':
            default:
                return $this->writeToFilesystem(self::FS_PATH . $this->id);
        }
    }

    private function writeToPostgreSQL () {
        $sql  = 'UPDATE ' . pg_escape_identifier(self::PPL_TBL) . ' SET';
        $sql .= ' gender=$1, personal_subjective=$2, personal_objective=$3, possesive=$4, reflexive=$5';
        $sql .= ' WHERE id=$6;';
        $result = pg_query_params($this->db_connection, $sql, array(
            pg_escape_string($this->gender),
            pg_escape_string($this->personal_subjective),
            pg_escape_string($this->personal_objective),
            pg_escape_string($this->possesive),
            pg_escape_string($this->reflexive),
            pg_escape_string($this->id)
        ));
        if (!$result) {
            throw new Exception('Failed to persist new data to PostgreSQL. Database error: ' . pg_last_error());
            return false;
        } else {
            return true;
        }
    }

    private function writeToFilesystem ($path) {
        $fh = new Config_Lite();
        try {
            $fh->write($path, array(
                'gender' => $this->gender,
                'personal_subjective' => $this->personal_subjective,
                'personal_objective' => $this->personal_objective,
                'possesive' => $this->possesive,
                'reflexive' => $this->reflexive
            ));
            return true;
        } catch (Config_Lite_Exception $e) {
            throw new Exception('Failed to persist new data to filesystem.');
            return false;
        }
    }
}
