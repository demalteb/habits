<?php

namespace ui;

class Template {
    public function __construct($name, $data = null) {
        $this->scriptName = $_SERVER['SCRIPT_NAME'];
        $this->name = $name;
        $this->data = $data;
    }

    public function setObject($data) {
        $this->data = $data;
    }

    public function fetch() {
        ob_start();
        $this->display();
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    public function display() {
        require( CONFIG()->template->rootPath . DS . $this->name . '.phtml' ); // do NOT use require_once, we need to recursively require the same script!
    }

    public function includeTemplate($name, $data = null) {
        if ( $data === null ) {
            $data = $this->data;
        }
        $tpl = new Template($name, $data);
        $tpl->display();
    }

    public static function h($str) {
        return htmlentities($str);
    }
}
