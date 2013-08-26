<?php
ini_set('include_path', get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/pear');
require 'Config/Lite.php';

class PersonWithPronouns {
    var $id; // Their Facebook $user_id.

    const FS_PATH = 'people/'; // Used for filesystem storage.

    function PersonWithPronouns ($id) {
        if ( ! (int) $id ) { throw new Exception('Invalid User ID.'); }
        $this->id = $id;
        if (!file_exists(self::FS_PATH . $id)) {
            if (!touch(self::FS_PATH . $id)) {
                exit('Cannot create file ' . self::FS_PATH . $id);
            }
        } else {
            $x = new Config_Lite(self::FS_PATH . $id);
            foreach ($x as $k => $v) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    function persist () {
        $fh = new Config_Lite();
        try {
            $fh->write(self::FS_PATH . $this->id, array(
                'gender' => $this->gender,
                'personal_subjective' => $this->personal_subjective,
                'personal_objective' => $this->personal_objective,
                'possesive' => $this->possesive,
                'reflexive' => $this->reflexive
            ));
        } catch (Config_Lite_Exception $e) {
            // TODO.
        }
    }
}
