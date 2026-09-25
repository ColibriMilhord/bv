/**
 * Configuration de la feuille de style de l'administration.
 *
 * Tailwind lit les écrans d'administration et n'emporte dans le fichier
 * produit que les classes qui y figurent réellement : 28 Ko au lieu des
 * 3 Mo de la bibliothèque complète.
 *
 * Conséquence à connaître : une classe assemblée dans le code (par exemple
 * 'bg-' + couleur) est invisible pour cette analyse et ne sera pas produite.
 * Les écrans n'en contiennent aucune ; si vous en ajoutez une, écrivez la
 * classe entière dans chaque branche du test plutôt que de la composer.
 */
module.exports = {
  content: ['../../admin/**/*.php'],
  theme: { extend: {} },
  plugins: [],
};
