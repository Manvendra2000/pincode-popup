<?php

class RoundRobinAPI {

    private $servers;
    private $counter_file;

    public function __construct($servers, $counter_file) {
        $this->servers = $servers;
        $this->counter_file = $counter_file;

        if (!file_exists($counter_file)) {
            file_put_contents($counter_file, '0');
        }
    }

    public function get_next_server() {
        $counter = (int)file_get_contents($this->counter_file);
        $selected = $this->servers[$counter % count($this->servers)];

        $counter++;
        file_put_contents($this->counter_file, $counter);

        return $selected;
    }
}

?>