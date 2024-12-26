<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$apiendpoint = get_config('block_essay_scoring', 'apiendpoint');
echo '' . $apiendpoint . '';
