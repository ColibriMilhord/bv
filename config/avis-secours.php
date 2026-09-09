<?php
/**
 * config/avis-secours.php — Avis affichés tant que l'API Google n'est pas
 * configurée, ou si Google ne répond pas.
 *
 * Ce fichier se modifie à la main. Il ne sert que de filet : dès que
 * 'GOOGLE_PLACES_API_KEY' et 'GOOGLE_PLACE_ID' sont renseignés dans
 * config/secrets.php, la note, le compteur et les trois derniers avis sont
 * repris automatiquement de Google, et ce fichier n'est plus consulté.
 *
 * Voir config/avis.php et docs/seo-ia/avis-google.md
 *
 * Champs d'un avis :
 *   auteur     prénom et initiale du nom, jamais le nom complet
 *   texte      extrait de l'avis, environ 260 caractères au maximum
 *   note       de 1 à 5
 *   horodatage date de publication (timestamp Unix) ; laisser null si
 *              inconnue, la mention 'date' sera alors utilisée telle quelle
 *   date       mention affichée si l'horodatage est inconnu
 */

return [
    // Note moyenne et nombre total d'avis affichés sur la page Google.
    // À reporter à la main tant que l'API n'est pas branchée.
    'note'  => 5.0,
    'total' => 102,

    'avis' => [
        [
            'auteur'     => 'Rob B.',
            'initiale'   => 'R',
            'texte'      => "L'une des plus belles vues de France. Rien ne peut vous préparer à ces panoramas, les photos ne leur rendent pas justice. Il y a tout ce que l'on peut souhaiter dans une maison de vacances, et tout est de la plus haute qualité.",
            'note'       => 5,
            'horodatage' => null,
            'date'       => 'Avis Google',
        ],
        [
            'auteur'     => 'Marielle V.',
            'initiale'   => 'M',
            'texte'      => "Simplement un paradis. Une cuisine ouverte où l'on ne manque de rien, des chambres magnifiques, une clarté et des nuits douces merveilleuses.",
            'note'       => 5,
            'horodatage' => null,
            'date'       => 'Avis Google',
        ],
        [
            'auteur'     => 'Ansar A.',
            'initiale'   => 'A',
            'texte'      => "Un endroit que l'on n'a pas envie de quitter, et que l'on a envie de partager.",
            'note'       => 5,
            'horodatage' => null,
            'date'       => 'Avis Google',
        ],
    ],
];
