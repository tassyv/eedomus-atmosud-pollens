<?

// Version v1.0
// Ce script Donne le niveau d'exposition aux pollens en région PACA
// Source AtmoSud

// Exemple d'appel du script avec variables: http://localhost/script/?exec=airpaca-pollens.php

// Le résultat est obtenu sous forme XML

// RécupÃ©ration des données
$url = "https://www.airpaca.org/allergie-pollen/indice-pollinique";
$page = httpQuery($url, 'GET');

// Parsing
$drupalsettings = strpos($page, 'Drupal.settings, {"basePath"');
$jsonarraystart = strpos($page, "{", $drupalsettings);
$jsonarrayend = strpos($page, "</script>", $jsonarraystart);
$jsonarray = substr($page, $jsonarraystart, $jsonarrayend - $jsonarraystart - 3);
$pollenstart = strpos($jsonarray, '"pollens');
$pollenend = strpos($jsonarray, ']', $pollenstart);
$pollenjson = substr($jsonarray, $pollenstart + 10, $pollenend - $pollenstart - 9);
$xml = jsonToXML($pollenjson);


// Génération de l'XML
$content_type = 'text/xml';
sdk_header($content_type);
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";
echo "<IndicesPolliniques>";
for($i = 1; $i <= xpath($xml, "count(//entry)") ; $i++)
{
    $tooltip = xpath($xml,"//entry[$i]/tooltip");
    $tooltip = str_replace("\/", '/', $tooltip);
    $tooltip = str_replace("\u003C", '<', $tooltip);
    $tooltip = str_replace("\u003E", '>', $tooltip);
    $tooltip = str_replace("\u0022", '"', $tooltip);
    $tooltip = str_replace("\u00e9", 'é', $tooltip);
    $tooltip = "<html>".$tooltip."</html>";
    echo "<Element>";
    $ville = xpath($tooltip, "substring-after(//p[1], ': ')");
    echo "<Ville>".$ville."</Ville>";
    echo "<Indice>".substr(xpath($xml,"//entry[$i]/class"), -1)."</Indice>";
    echo "<Pollens>";
    for($j = 1; $j <= xpath($tooltip, "count(//li)") ; $j++)
    {
        echo "<Pollen>".xpath($tooltip, "//li[$j]")."</Pollen>";
    }
    echo "</Pollens>";
    echo "</Element>";
}
echo "</IndicesPolliniques>";
?>
