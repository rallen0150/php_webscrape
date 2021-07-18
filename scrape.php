<?php

/*
 * This is a web scraping test that will pull the top 20 NBA scorers of all time based on a single season result
 *    This is a terminal script that allows the user to input the specific stat (Points, Rebounds or Assists)
*/

// Functions to create a name and points array to combine at a later point
function create_array(array $listItems, string $type) {
    $arr = [];
    $index = 0; // counter to get the top 20 for stat
    $pos = 0; // counter that is the array postion for the name and year
    foreach ($listItems as $item) {
        $break_at = 20;
        // Once we get the top 20 players, stop the loop
        $break = (sizeof($arr) == $break_at && $type == "stats") || ($pos == $break_at && $type == "name");
        if ($break) break;
        if ($type == "name") {
            if ($index%2==0) {
                // All players will have an anchor tag with them, so just pull the info from only between the anchor tags
                preg_match('@<a\b[^>]*>(.*?)</a>@', $item, $nameMatch);
                $name = (string)$nameMatch[1];
                // Store in the name_arr array for later iteration
                $arr[$pos][0] = $name;
            } else {
                $arr[$pos][1] = $item;
                $pos++;
            }
        } else {
            $arr[] = $item;
        }
        $index++;
    }
    return $arr;
}


$stat_list = ["points", "rebounds", "assists"]; // The only valid stat list
$url_stat = "";
$check_stat = false;
// Keep the script running until the user inputs a valid stat number
while ($check_stat == false) {
    $stat = (string) readline("What stat do you want to look up?\nPlease enter the number or stat name: points (1), rebounds (2), assists (3)\n"); // User inputs the stat they want to lookup

    // Depending on the input, we check to see which input is valid and what stat it is associated with
    switch ($stat) {
        case 1:
        case "points":
        case "point":
            $stat = "points";
            $url_stat = "pts";
            break;
        case 2:
        case "rebounds":
        case "rebound":
            $stat = "rebounds";
            $url_stat = "trb";
            break;
        case 3:
        case "assists":
        case "assist":
            $stat = "assists";
            $url_stat = "ast";
            break;
    }
    if (!in_array($stat, $stat_list)) {
        echo "\nPlease enter a valid stat number!\n"; 
    } else {
        $check_stat = true; // When a valid input is given, then break the loop
    }
}


$html = file_get_contents("https://www.basketball-reference.com/leaders/{$url_stat}_per_g_season.html");
$start = stripos($html, 'id="stats_NBA"');
$end = stripos($html, '</table>', $offset = $start);
$length = $end - $start;
$htmlSection = substr($html, $start, $length);

// Separate all the cells based in the stats_NBA table
preg_match_all('@<td>(.+)</td>@', $htmlSection, $matches);
$listNames = $matches[1];
$name_arr = create_array($listNames, "name");

// Now we get the stats average for those players
preg_match_all('@<td align="right">(.+)</td>@', $htmlSection, $matches);
$listStats = $matches[1];
$points_arr = create_array($listStats, "stats");

// Should have the same number of points and names, so combine the arrays to form a multidimensional array
$stats_arr = array_map(null, $name_arr, $points_arr);

// Title-case the stat for the output
$disp_stat = ucwords($stat);

echo "\n-------------- The Top 20 {$disp_stat} Average in an NBA Season are --------------\n";
foreach ($stats_arr as $info) {
    // echo "{$info[0][0]}\t{$info[1]}\n";
    echo str_pad($info[0][0], 40, " ", STR_PAD_RIGHT)."\t".str_pad($info[0][1], 10, " ", STR_PAD_RIGHT)."\t".str_pad($info[1], 8, " ", STR_PAD_RIGHT)."\n";
}