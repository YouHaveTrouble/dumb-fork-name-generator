<?php
$seed = isset($_GET["seed"]) ? base64_decode($_GET["seed"]) : '';
$seedArray = explode(',', $seed);
$seedValues = [];

foreach ($seedArray as $seedValue) {
    $entry = explode(':', $seedValue);
    if (count($entry) !== 2) {
        $seedValues = [];
        break;
    }
    $seedValues[$entry[0]] = $entry[1];
}

$combinations = array(
    "fork" => json_decode(file_get_contents('forks.json')),
    "prefix" => json_decode(file_get_contents('prefixes.json')),
    "thing" => json_decode(file_get_contents('things.json')),
    "suffix" => json_decode(file_get_contents('suffixes.json')),
    "extra" => json_decode(file_get_contents('extras.json')),
    "version" => json_decode(file_get_contents('versions.json')),
    "price" => json_decode(file_get_contents('price.json')),
    "perk" => json_decode(file_get_contents('perks.json')),
);
$sentences = json_decode(file_get_contents('sentences.json'));

// select sentence
try {
    if (!isset($seedValues[0])) throw new Exception("not a valid sentence index");
    $sentence = $sentences[$seedValues[0]];
} catch (Exception $exception) {
    $sentenceIndex = rand(0, count($sentences) - 1);
    $sentence = $sentences[$sentenceIndex];
    $seedValues[0] = $sentenceIndex;
}

// search for placeholders
$toReplace = [];
preg_match_all('/\[.+?]/', $sentence, $toReplace);

$i = 1; // index 0 is sentence number, so start with 1
foreach ($toReplace[0] as $key) {
    $index = str_replace(['[', ']'], "", $key);
    if (strcmp($key, $index) === 0) continue; // don't attempt to replace if there's no []
    try {
        if (!isset($seedValues[$i])) throw new Exception("seed value not set");
        if (!isset($combinations[$index][$seedValues[$i]])) throw new Exception("not a valid placeholder index");
        $combo = $combinations[$index][$seedValues[$i]];
    } catch (Exception $exception) {
        $placeholderIndex = rand(0, count($combinations[$index]) - 1);
        $combo = $combinations[$index][$placeholderIndex];
        $seedValues[$i] = $placeholderIndex;
    }
    $sentence = preg_replace('/\['.$index.']/', $combo, $sentence, 1);
    $i++;
}
$seedArray = [];
$keyIndex = 0;
foreach ($seedValues as $seedValue) {
    $seedArray[] = $keyIndex++.':'.$seedValue;
}
$seed = implode(',', $seedArray);
?>

<?php if (!isset($_GET["plain"])) : ?>
<html lang="en">
<head>
    <title>dumb fork name generator</title>
    <meta property="og:title" content="dumb fork name generator"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="https://dumbforks.yht.one/"/>
    <meta property="og:description" content="<?php echo($sentence) ?>"/>
    <meta property="twitter:title" content="dumb fork name generator"/>
    <meta property="twitter:description" content="<?php echo($sentence) ?>"/>
    <style>
        h6 {
            text-align: center;
            font-weight: normal;
            color: #777;
        }

        a {
            color: #55C;
        }

        h3 {
            text-align: center;
            font-family: serif;
            font-weight: normal;
            font-size: 24px;
        }

        h1 {
            text-align: center;
            font-family: sans-serif;
        }
    </style>
</head>
<body>
<h3>Dumb fork name generator</h3>
<h1>
    <?php endif; ?>
    <?php echo($sentence) ?>
    <?php if (!isset($_GET["plain"])) : ?>
</h1>
<h3><a href="/">Give it one more try!</a></h3>
<h3><a href="/?seed=<?php echo(base64_encode($seed)) ?>">Share</a></h3>
</body>
<?php endif; ?>
