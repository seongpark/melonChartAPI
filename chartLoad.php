<?php
header('Content-Type: application/json');

$url = "https://www.melon.com/chart/index.htm";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$html = curl_exec($ch);
curl_close($ch);

libxml_use_internal_errors(true);
$doc = new DOMDocument();
$doc->loadHTML($html);
$xpath = new DOMXPath($doc);

$tables = $xpath->query("//table");
$classes = ['lst50', 'lst100'];

$chartData = [];
$rank = 1;

foreach($tables as $table){
    foreach($classes as $class){
        $trs = $xpath->query(".//tr[contains(@class,'$class')]", $table);
        foreach($trs as $tr){
            $wraps = $xpath->query(".//td//div[contains(@class,'wrap_song_info')]", $tr);
            foreach($wraps as $wrap){
                $artistNodes = $xpath->query(".//div[contains(@class,'wrap_artist')]", $wrap);
                foreach($artistNodes as $artistNode){
                    $artistNode->parentNode->removeChild($artistNode);
                }
                $buttonNodes = $xpath->query(".//button", $wrap);
                foreach($buttonNodes as $btn){
                    $btn->parentNode->removeChild($btn);
                }

                $spans = $xpath->query(".//span", $wrap);
                $info = [];
                foreach($spans as $span){
                    $text = trim($span->nodeValue);
                    if($text !== "더보기" && $text !== ""){
                        $info[] = $text;
                    }
                }

                $imgNode = $xpath->query(".//img", $tr);
                $imgSrc = $imgNode->length > 0 ? $imgNode[0]->getAttribute('src') : "";

                if(!empty($info)){
                    $chartData[] = [
                        'rank' => $rank,
                        'songs' => isset($info[0]) ? $info[0] : "",
                        'artist' => isset($info[1]) ? $info[1] : "",
                        'image' => $imgSrc
                    ];
                    $rank++;
                }
            }
        }
    }
}

echo json_encode($chartData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
