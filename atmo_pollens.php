<?

// Version v2.1
// Ce script Donne le niveau d'exposition aux pollens en région PACA et AURA
// Source AtmoSud / AtmoAURA

// Exemple d'appel du script avec variables: http://localhost/script/?exec=atmo_pollens.php&ville=

// Le résultat est obtenu sous forme XML

// Récupération des données
$paramville = getArg('ville');
$villespaca = array('Aix-en-Provence', 'Gap', 'Marseille', 'Nice', 'Toulon', 'Avignon');
$villesaura = array('Annemasse', 'Aurillac', 'Bourg en Bresse', 'Chambéry', 'Clermont-Ferrand', 'Grenoble', 'Le Puy-en-Velay', 'Lyon', 'Roussillon', 'Saint-Etienne');

if (in_array($paramville, $villespaca))
{
	$url = "https://www.airpaca.org/allergie-pollen/indice-pollinique";
}
else if (in_array($paramville, $villesaura))
{
	$url = "https://www.atmo-auvergnerhonealpes.fr/allergie-pollen/indice-pollinique";
}

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
echo "<RisqueAllergique>";
for($i = 1; $i <= xpath($xml, "count(//entry)") ; $i++)
{
    $tooltip = xpath($xml,"//entry[$i]/tooltip");
    $tooltip = str_replace("\/", '/', $tooltip);
    $tooltip = str_replace("\u003C", '<', $tooltip);
    $tooltip = str_replace("\u003E", '>', $tooltip);
    $tooltip = str_replace("\u0022", '"', $tooltip);
    $tooltip = str_replace("\u00e9", 'é', $tooltip);
    $tooltip = str_replace("\u00e2", 'â', $tooltip);
    $tooltip = "<html>".$tooltip."</html>";
    $ville = xpath($tooltip, "substring-after(//p[1], ': ')");
    if($ville == $paramville)
    {
        echo "<Ville>".$ville."</Ville>";
	$indice = substr(xpath($xml,"//entry[$i]/class"), -1);
        if($indice == 'D')
            $indice = 'ND';
        echo "<Indice>".$indice."</Indice>";
            $pollens = "<Pollens>";
    
        for($j = 1; $j <= xpath($tooltip, "count(//li)") ; $j++)
        {
            echo "<Pollen>".xpath($tooltip, "//li[$j]")."</Pollen>";
    	if($j > 1)
    	{
		    $pollens = $pollens.',';
	    }
	    $pollens = $pollens.xpath($tooltip, "//li[$j]");
        }
        $pollens = $pollens."</Pollens>";
        echo $pollens;
    }
}
echo "</RisqueAllergique>";
?>
