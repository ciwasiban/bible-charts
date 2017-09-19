<?php
define("IS_DEBUG_ONE_ITEM", false);
//define("IS_DEBUG", true);
define("IS_DEBUG", false);
define("VENDOR_DIR", __DIR__ . "/vendor/simplehtmldom_1_5/");

require "simple_html_dom.php";
require "RandomColor.php";

use \Colors\RandomColor;

if (IS_DEBUG) {
    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTeo6NONZFWdM8hpKuu4dyMP6Scx8qK1lz_fQG1PjBQdkQ3gXJm5IQF-Gh4Wzl8gG2HBhmdYGmsMxne/pubhtml?gid=664118286&single=true";
} else {
    if (empty($_POST)) {
        echo "there's no post";
    }

    $url = $_POST['url'];
    if (empty($url)) {
        echo "there's no url";
    }
}

$data = getData($url);
$data2 = dataHandler($data);
echo $str = generateJsData($data2);

// Generate data for javascript
function generateJsData($data) {
    $jsData = $indexOfName = array();
    $i = 0;

    $colors = RandomColor::many(30, array(
            'luminosity' => array('light'),
            'hue' => 'random'
            ));
    $colors2 = array('#7E8F74', '#F0E6A7', '#EBB88A', '#ff9999', '#ff4d4d', '#ccccff', '#6666ff', '#ebccff', '#c266ff', '#ccffe6', '#00e673', '#f4a742', '#edbb7b');

    if (is_array($data)) {
        foreach ($data AS $chart => $row) {
            $item = array();
            $i++;
            $item['type'] = "stackedBar";
            $item['showInLegend'] = true;
            $item['name'] = $chart;
            $item['axisYType'] = "secondary";
            //$item['color'] = $colors[$i%30];
            $item['color'] = $colors2[$i%11];
            $item['dataPoints'] = array();
            foreach ($row AS $name => $num) {
                //$mm = ["小潘"=>3, "曉芬"=>1, "佳珊"=>2];
                //array_push($item['dataPoints'], ['y' => $num, 'label' => $mm[$name]]);
                array_push($item['dataPoints'], ['y' => $num, 'label' => $name]);
            }
            array_push($jsData, $item);
        }
    }
    if (IS_DEBUG) {
        echo "==== print js data ==== \n";
        print_r($jsData);
    }
    return json_encode($jsData);
}

// Handler Data for what I want
function dataHandler($data) {
    $userTotal = [];
    if (empty($data)) {
        // there's no data.
    } else {
        foreach ($data AS $row) {
            $user = $row[5];
            $num = $row[4];
            $chart = $row[3];
            $date = $row[2];

            if (!isset($userTotal[$chart])) {
                $userTotal[$chart] = ['小潘'=> 0, '曉芬'=> 0, '佳珊'=> 0];
            }

            if (!isset($userTotal[$chart][$user])) {
                $userTotal[$chart][$user] = 0;
            }
            $userTotal[$chart][$user] += $num;
        }
        if (IS_DEBUG) {
            echo "==== print data ====\n";
            print_r($userTotal);
        }
    }
    return $userTotal;
}

// Fetch data from Google Sheets
function getData($url) {
    // Create DOM from URL or file
    unset($html);
    $data = [];

    $html = file_get_html($url);
    $content = $html->plaintext;

    if (empty($content)) {
        //echo "no data: " . $number . "\n";
    } else {
        // get title
        $tr = $html->find('tr');
        $data = array();
        $itemNum = 0;
        foreach ($tr AS $item) {
            $tdNum = 0;
            $itemNum++;

            if ($itemNum <= 3) {
                continue;
            }

            foreach($item->find('td') AS $td) {
                $tdNum++;
                $data[$itemNum][$tdNum] = $td->plaintext;
            }

            // for debug
            if (IS_DEBUG) {
                if (is_array($data[$itemNum])) {
                    echo(implode(" || " , $data[$itemNum]) . "\n");
                }
            }
        }
    }
    return $data;
}
