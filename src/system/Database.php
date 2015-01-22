<?php

class Database implements SystemModule {

    const FIELD_TYPE_INT = 128;
    const FIELD_TYPE_FLOAT = 64;
    const FIELD_TYPE_STRING = 32;
    const FIELD_TYPE_TEXT = 16;
    const FIELD_TYPE_DATE = 8;
    const PRIMARY_KEY = 4;
    const AUTOINCREMENT = 2;
    const NOTNULLVAL = 1;

    public static function schema_installer($schema) {
        $keywords = array("INT", "FLOAT", "VARCHAR(255)", "TEXT", "DATETIME", "PRIMARY KEY", "AUTO_INCREMENT", "NOT NULL");
        foreach ($schema as $table => $attributes) {
            if (self::table_exists($table)) {
                throw new Exception_Database("Table exists");
            }
        }
        foreach ($schema as $table => $attributes) {
            $sql = "CREATE TABLE IF NOT EXISTS $table(";
            $i = 0;
            foreach ($attributes as $key => $infos) {
                $i++;
                $sql .="\n`$key` ";
                $row = str_pad(decbin($infos), 8, "0", STR_PAD_LEFT);
                $splited_row = str_split($row);
                foreach ($splited_row as $k => $r) {
                    if ($r == 1) {
                        $sql .=$keywords[$k] . " ";
                    }
                }
                if (count($attributes) > $i) {
                    $sql.=",";
                }
            }
            $sql.=");";
            self::execute($sql);
        }
        foreach ($schema as $table => $attributes) {
            if (!self::table_exists($table)) {
                self::schema_uninstaller($schema);
                throw new Exception_Database("Table does not exist");
            }
        }
    }

    public static function schema_uninstaller($schema) {

        foreach ($schema as $table => $k) {
            self::execute("DROP TABLE IF EXISTS $table CASCADE");
        }
        foreach ($schema as $table => $k) {
            if (self::table_exists($table)) {
                throw new Exception_Database("Table exists");
            }
        }
    }

    public static function table_exists($tablename) {
        $database = "jinn";
        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '$database' AND table_name = '$tablename' LIMIT 1;";
        return self::getAll($sql) != false;
    }

    public static $connector = null;

    public function info() {
        _Security::version(0, 1);
        return array(
            "name" => "Database",
            "readablename" => "Database Module"
        );
    }

    public function priority() {
        return -100;
    }

    public function system_init() {
        if (self::$connector == null) {
            $servername = "localhost";
            $dbname = "jinn";
            $username = "jinn";
            $password = "jAdNK23AHMmupzWE";
            self::$connector = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        }
    }

    public static function execute($sql) {
        return self::$connector->query($sql);
    }

    public static function getAll($sql) {
        $exec = self::$connector->query($sql);
        if ($exec != false) {
            return $exec->fetchAll(PDO::FETCH_OBJ);
        }
        return false;
    }

    public static function getRow($sql) {
        if ($exec != false) {
            return $exec->fetch(PDO::FETCH_OBJ);
        }
        return false;
    }

    public static function getValue($sql) {
        if ($exec != false) {
            return $exec->fetch(PDO::FETCH_COLUMN);
        }
        return false;
    }

}

class Exception_Database extends Exception {
    
}