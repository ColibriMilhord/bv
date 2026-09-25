<?php
/**
 * Liste les icônes employées par l'administration, pour régénérer le
 * sous-ensemble de police servi par le site.
 *
 * Usage : php tools/habillage-admin/liste-icones.php
 * Sortie : les noms séparés par des virgules, à coller dans l'adresse
 *          décrite par le README de ce dossier.
 *
 * Les noms écrits en dur dans le HTML sont trouvés automatiquement. Ceux
 * produits par du PHP ne peuvent pas l'être : ils sont déclarés ci-dessous.
 * Si vous ajoutez une icône choisie par une condition, ajoutez-la ici aussi,
 * sans quoi elle s'affichera en toutes lettres à l'écran.
 */

$dynamiques = ['add_circle']; // admin/tarifs.php : edit ou add_circle

$noms = $dynamiques;
foreach (glob(__DIR__ . '/../../admin/*.php') as $fichier) {
    $source = file_get_contents($fichier);
    if (preg_match_all('#material-symbols-outlined[^>]*>(.*?)</span>#s', $source, $trouves)) {
        foreach ($trouves[1] as $contenu) {
            $contenu = trim($contenu);
            if (preg_match('/^[a-z0-9_]+$/', $contenu)) {
                $noms[] = $contenu;
            } elseif ($contenu !== '' && strpos($contenu, '<?php') !== false) {
                fwrite(STDERR, "icône calculée dans " . basename($fichier) . " : " . $contenu . "\n");
            }
        }
    }
}

$noms = array_unique($noms);
sort($noms);
fwrite(STDERR, count($noms) . " icônes\n");
echo implode(',', $noms), "\n";
