<?php

if (class_exists('JEventDispatcher', false)) {
    $dispatcher = JEventDispatcher::getInstance();
} else {
    $dispatcher = JDispatcher::getInstance();
}

$dispatcher->trigger('onInitN2Library');