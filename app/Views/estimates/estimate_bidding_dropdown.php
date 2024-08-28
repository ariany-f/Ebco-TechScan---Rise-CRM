<?php

$estimate_bidding_dropdown = array(
    array("id" => "", "text" => "- " . app_lang("is_bidding") . " -"),
    array("id" => "1", "text" => app_lang("Yes")),
    array("id" => "0", "text" => app_lang("No")),
);
echo json_encode($estimate_bidding_dropdown);
