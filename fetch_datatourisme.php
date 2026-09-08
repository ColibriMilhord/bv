<?php
$url_with_braces = "https://diffuseur.datatourisme.fr/webservice/2e6202508df1eb9e27ff97e2e2670006/{fb5fbfd8-4771-4867-b548-3645bea94feb}";
$url_without_braces = "https://diffuseur.datatourisme.fr/webservice/2e6202508df1eb9e27ff97e2e2670006/fb5fbfd8-4771-4867-b548-3645bea94feb";

function tryFetch($url) {
    echo "Fetching: " . $url . "\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // Mimic real browser
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Accept-Language: fr-FR,fr;q=0.9',
    ]);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP CODE: " . $code . "\n";
    if ($code == 200) {
        file_put_contents('dt_debug.json', substr($html, 0, 1500));
        echo "Saved to dt_debug.json\n";
    }
    curl_close($ch);
}

tryFetch($url_with_braces);
tryFetch($url_without_braces);
?>
