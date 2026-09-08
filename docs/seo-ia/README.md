# Référencement IA (GEO) — documentation

Travaux de référencement pour moteurs de réponse génératifs menés sur
bellevuedaveyron.fr, **consignés pour être réutilisés sur artifacile.fr**.

| Document | À lire quand |
|---|---|
| [`playbook-geo.md`](playbook-geo.md) | **Point d'entrée.** Méthode générique, principes, parcours d'audit, patrons de code, recette de vérification, et section d'adaptation à artifacile.fr. |
| [`audit-2026-09-08.md`](audit-2026-09-08.md) | Le cas concret : état des lieux, défauts relevés, correctifs appliqués, contrôles effectués, suites recommandées. |
| [`inventaire-images.md`](inventaire-images.md) | Feuille de route photo : quelles images remplacer, sous quel nom de fichier les déposer. |

## Où vit le code

| Fichier | Rôle |
|---|---|
| `config/seo.php` | Identité du site, rendu des balises `<head>`, constructeurs de nœuds JSON-LD, questions fréquentes. **Le fichier à recopier en premier sur un autre projet.** |
| `config/medias.php` | Registre des visuels : texte alternatif, image de repli, substitution par un fichier local. |
| `config/agenda.php` | Données externes : cache, calcul de distance, filtrage par rayon, socle éditorial de repli. |
| `robots.txt` | Autorisations d'accès des robots, dont ceux des moteurs de réponse. |
| `sitemap.xml` | Pages publiques et dates de mise à jour. |
| `llms.txt` | Fiche factuelle du site destinée aux agents conversationnels. |

## Entretien courant

À faire à chaque évolution du site :

- **Contenu modifié** → mettre à jour `lastmod` dans `sitemap.xml`.
- **Fait qui change** (tarif, capacité, contact) → le corriger dans
  `config/seo.php` et dans `llms.txt`, jamais ailleurs.
- **Nouvelle page** → l'ajouter à `sitemap.xml`, à `llms.txt`, appeler
  `seo_head()` et poser un graphe JSON-LD.
- **Nouvelle photo de lieu** → la déposer dans `images/decouvrir/` au nom du
  slug (voir l'inventaire) ; aucun code à modifier.
- **Tous les trimestres** → poser les questions cibles à ChatGPT, Perplexity et
  Google, et vérifier dans les journaux serveur le passage de `GPTBot`,
  `ClaudeBot` et `PerplexityBot`.
