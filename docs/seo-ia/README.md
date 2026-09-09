# Référencement IA (GEO) — documentation

Travaux de référencement pour moteurs de réponse génératifs menés sur
bellevuedaveyron.fr, **consignés pour être réutilisés sur artifacile.fr**.

| Document | À lire quand |
|---|---|
| [`playbook-geo.md`](playbook-geo.md) | **Point d'entrée.** Méthode générique, principes, parcours d'audit, patrons de code, recette de vérification, et section d'adaptation à artifacile.fr. |
| [`audit-2026-09-08.md`](audit-2026-09-08.md) | Le cas concret : état des lieux, défauts relevés, correctifs appliqués, contrôles effectués, suites recommandées. |
| [`inventaire-images.md`](inventaire-images.md) | Feuille de route photo : quelles images remplacer, sous quel nom de fichier les déposer. |
| [`avis-google.md`](avis-google.md) | Brancher la note, le compteur et les trois derniers avis sur l'API Google Places. |
| [`installer-python-windows.md`](installer-python-windows.md) | Pas à pas complet, de l'installation de Python sur Windows à la mise en ligne des photos. |

## Où vit le code

| Fichier | Rôle |
|---|---|
| `config/seo.php` | Identité du site, rendu des balises `<head>`, constructeurs de nœuds JSON-LD, questions fréquentes. **Le fichier à recopier en premier sur un autre projet.** |
| `config/medias.php` | Registre des visuels : texte alternatif, image de repli, substitution par un fichier local. |
| `config/agenda.php` | Données externes : cache, calcul de distance, filtrage par rayon, socle éditorial de repli. |
| `config/notifications.php` | Destinataires des demandes du formulaire, réglables dans « Paramètres du Gîte ». Expéditeur non modifiable. |
| `config/stats.php` | Mesure d'audience interne, sans cookie : enregistrement des visites, résolution du pays, restitution. |
| `config/annonces.php` | Bandeau d'information publié depuis l'administration, avec fenêtre de dates. |
| `config/avis.php` | Avis Google : note, compteur et trois derniers avis, avec cache 12 h et repli sur `config/avis-secours.php`. |
| `config/env.php` | Lecture des secrets : variables d'environnement, puis `config/secrets.php` (non versionné). Aucun mot de passe dans le dépôt. |
| `config/secrets.example.php` | Modèle à recopier en `config/secrets.php` sur le serveur. |
| `tools/generer-inventaire-images.php` | Régénère l'inventaire des visuels depuis le registre. |
| `tools/unsplash_photos.py` | Recherche et télécharge via l'API Unsplash les illustrations des cartes encore dépourvues de photo, et tient à jour les crédits obligatoires. |
| `robots.txt` | Autorisations d'accès des robots, dont ceux des moteurs de réponse. |
| `sitemap.xml` | Pages publiques et dates de mise à jour. |
| `llms.txt` | Fiche factuelle du site destinée aux agents conversationnels. |

## Mise en production

Marche à suivre complète et ordonnée : [`../DEPLOIEMENT.md`](../DEPLOIEMENT.md).
À suivre dans l'ordre — le fichier de secrets doit exister sur le serveur
**avant** le téléversement des fichiers.

## Mise en service sur un serveur

```bash
cp config/secrets.example.php config/secrets.php
# puis renseigner les valeurs réelles dans config/secrets.php
```

Sans ce fichier (ou sans les variables d'environnement équivalentes), la base
et l'envoi d'e-mails ne fonctionnent pas : le site public s'affiche sans
calendrier ni tarifs, l'administration répond 503.

## Entretien courant

À faire à chaque évolution du site :

- **Contenu modifié** → mettre à jour `lastmod` dans `sitemap.xml`.
- **Fait qui change** (tarif, capacité, contact) → le corriger dans
  `config/seo.php` et dans `llms.txt`, jamais ailleurs.
- **Nouvelle page** → l'ajouter à `sitemap.xml`, à `llms.txt`, appeler
  `seo_head()` et poser un graphe JSON-LD.
- **Nouvelle photo de lieu** → la déposer dans `images/decouvrir/` au nom du
  slug (voir l'inventaire) ; aucun code à modifier. Faute de photo propre :
  `python3 tools/unsplash_photos.py search` puis `download`.
- **Mot de passe changé** → le reporter dans `config/secrets.php` uniquement.
- **Tous les trimestres** → poser les questions cibles à ChatGPT, Perplexity et
  Google, et vérifier dans les journaux serveur le passage de `GPTBot`,
  `ClaudeBot` et `PerplexityBot`.
