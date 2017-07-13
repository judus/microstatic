<?php

foreach (glob(__DIR__ . "/*.php") as $filename) {
    /** @noinspection PhpIncludeInspection */
    require_once $filename;
}
