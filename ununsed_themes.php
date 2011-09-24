#!/usr/bin/php

<?php
require_once 'credentials.php';
$option_tables = array();
$blogs = array();
$used_themes = array();
$templates_unused = array();

$sql = "SELECT * FROM information_schema.tables";
$sql .= " WHERE TABLE_NAME LIKE 'wp_%_options';";
$dh = mysql_connect($DBhost, $DBuname, $DBpass) or die('Mysql connect Failed');
mysql_select_db($DBname, $dh);
$result = mysql_query($sql, $dh);

while($row = mysql_fetch_array($result)) {
    array_push($option_tables, $row['TABLE_NAME']);
}

foreach($option_tables as $option_table) {
    $sql = "SELECT option_value, option_name from $option_table";
    $sql .= " WHERE option_name = 'template'";
    $sql .= " OR option_name = 'siteurl';";
    $result = mysql_query($sql, $dh);
    while($row = mysql_fetch_array($result)) {
        // Store the blog url as well as the theme in case we need it later
        $blog_option[$row['option_name']] = $row['option_value'];
    }
    // Store blog url and theme
    array_push($blogs, $blog_option);
}
mysql_close($dh);

// Get all the available themes
$command = "ls $WPthemedir";
$folders = shell_exec($command);
$available_themes = explode("\n",$folders);

foreach($blogs as $blog) {
    assert(in_array($blog['template'], $available_themes));
    array_push($used_themes, $blog['template']);
}
$unique_themes_used = array_unique($used_themes);

foreach($available_themes as $theme) {
    if (!in_array($theme, $unique_themes_used))
        array_push($templates_unused, $theme);
}
echo "Unused templates:\n";
foreach($templates_unused as $template) {
    if ($template != 'index.php')
        echo "$template\n";
}
?>
