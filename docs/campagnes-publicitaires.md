# Mesurer ce que rapporte une publicité, sans traceur

Vous avez reçu un message vous expliquant qu'un Pixel Facebook vous manque et
que vous « perdez de l'argent ». Ce document explique pourquoi ce n'est pas le
cas à votre échelle, et ce que le site fait à la place.

---

## Pourquoi pas de Pixel

Le budget publicitaire est d'environ **100 € par trimestre**, soit une
trentaine d'euros par mois. À ce niveau, un Pixel n'apporte rien :

- l'algorithme de Facebook a besoin d'environ **50 conversions par semaine et
  par ensemble de publicités** pour sortir de sa phase d'apprentissage. Trente
  euros par mois en produisent quelques-unes par mois, pas par semaine ;
- le reciblage exige une audience d'au moins un millier de personnes ayant
  visité le site. Le volume n'y est pas ;
- un Pixel est un traceur non exempté : il impose un **bandeau de consentement**
  sur chaque visite. Le site n'en a pas besoin aujourd'hui, et un bandeau sur un
  site de gîte 5 étoiles n'est pas neutre ;
- entre 30 et 60 % des visiteurs refusent. Le Pixel ne verrait donc qu'une
  partie du trafic, bandeau ou pas.

> **Un argument de vente à connaître.** « L'API Conversions contourne le
> consentement » est faux. Elle contourne les bloqueurs de publicité, pas le
> droit : mêmes données personnelles, mêmes obligations.

Le jour où le budget mensuel se compte en centaines d'euros, la question se
repose. Pas avant.

---

## Ce que le site fait à la place

Il compte les visites venues de chaque annonce, et les demandes qui en
découlent — **sans traceur, sans cookie, donc sans bandeau**.

Le principe tient en une phrase : l'information n'est pas prélevée sur le
visiteur, elle est **écrite par vous au bout du lien de votre publicité**.

### Comment étiqueter un lien

Au lieu de mettre `https://bellevuedaveyron.fr` dans votre annonce, mettez :

```
https://bellevuedaveyron.fr/?utm_source=facebook&utm_campaign=ete2026
```

- `utm_source` — d'où vient le clic : `facebook`, `instagram`, `google`…
- `utm_campaign` — le nom que **vous** donnez à l'annonce : `ete2026`,
  `aubrac`, `derniere-minute`…

Changez `utm_campaign` à chaque annonce : c'est ce qui permet de les comparer.
Sans accent ni espace, de préférence — ils sont acceptés, mais simplifiés à
l'enregistrement.

À défaut de paramètres, le site reconnaît aussi l'identifiant de clic laissé
par Facebook (`fbclid`) ou Google (`gclid`) : la source est alors notée, mais
pas l'annonce précise. D'où l'intérêt de les poser vous-même.

### Où lire le résultat

**Administration → Audience du site → Campagnes publicitaires**

| Campagne | Visites | Visiteurs | Demandes |
|---|---:|---:|---:|
| facebook / ete2026 | 140 | 118 | 4 |
| facebook / aubrac | 60 | 52 | 1 |
| instagram | 25 | 22 | 0 |

C'est la seule colonne qui compte : **Demandes**. Quatre demandes pour une
annonce et aucune pour une autre vous dit où remettre les trente euros du mois
suivant. Aucun Pixel ne vous en dira davantage à cette échelle.

---

## Les limites, dites franchement

- **Le rattachement vaut pour la journée.** L'empreinte du visiteur est
  renouvelée chaque nuit — c'est ce qui rend la mesure anonyme. Un visiteur qui
  clique lundi et écrit jeudi ne sera pas rattaché à l'annonce. L'essentiel des
  demandes partant dans la foulée de la visite, la perte est faible, mais elle
  existe.
- **Le comptage est le vôtre, pas celui de Facebook.** Les deux chiffres ne
  coïncideront jamais exactement. Celui-ci a l'avantage de porter sur des
  personnes réellement venues chez vous.
- **Rien n'est mesuré hors du site.** Un appel téléphonique consécutif à une
  annonce n'apparaîtra pas. Demandez simplement à vos interlocuteurs comment
  ils vous ont connu : c'est encore la mesure la plus fiable.

---

## Transposable à artifacile.fr

La règle générale : **avant d'installer un traceur, vérifier que le volume
justifie l'outil.** En dessous du seuil d'apprentissage d'un algorithme
publicitaire, un paramètre au bout d'une adresse et un décompte côté serveur
donnent la même information — sans bandeau de consentement, sans dépendance à
un tiers, et sans rien prélever sur le visiteur.
