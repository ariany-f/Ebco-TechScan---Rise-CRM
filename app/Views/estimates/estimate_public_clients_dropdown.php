<?php

$estimate_public_clients_dropdown = array(
    array("id" => "", "text" => "- " . app_lang("public_client") . " -"),
    array("id" => "1", "text" => app_lang("Yes")),
    array("id" => "0", "text" => app_lang("No")),
);
echo json_encode($estimate_public_clients_dropdown);
