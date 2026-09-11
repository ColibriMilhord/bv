/* js/script.js */

// --- 0. SYSTÈME DE NOTIFICATION ---
// Création du conteneur au chargement s'il n'existe pas
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
});

function showNotification(message, type = 'info') {
    const container = document.getElementById('toast-container');
    
    // Création de l'élément
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Icône selon le type
    let icon = 'info';
    if(type === 'success') icon = 'check_circle';
    if(type === 'error') icon = 'error'; // Ou 'warning' pour le min 3 nuits

    toast.innerHTML = `
        <span style="font-size:1.2rem; color:inherit;">${type === 'error' ? '⚠' : 'ℹ'}</span>
        <div>${message}</div>
    `;

    container.appendChild(toast);

    // Suppression automatique après 5 secondes
    setTimeout(() => {
        toast.classList.add('hide');
        toast.addEventListener('animationend', () => toast.remove());
    }, 5000);
}

// --- 1. GESTION DE L'INTRODUCTION ---
window.addEventListener('load', () => {
    const overlay = document.getElementById('intro-overlay');
    const body = document.body;
    const successModal = document.getElementById('successModal');
    const isReservationSuccess = successModal && successModal.classList.contains('active');

    if(overlay) {
        if (isReservationSuccess) {
            overlay.style.display = 'none'; 
            body.classList.remove('loading'); 
            if (history.replaceState) history.replaceState(null, null, window.location.pathname);
        } else {
            overlay.classList.add('animate');
            setTimeout(() => {
                overlay.classList.add('hidden');
                body.classList.remove('loading');
            }, 6800);
        }
    }
});

// --- 2. MENU MOBILE ---
window.addEventListener('scroll', function() {
    const header = document.getElementById('navbar');
    if (window.scrollY > 50) header.classList.add('scrolled'); 
    else header.classList.remove('scrolled');
});

function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('active');
}

/* ══════════════════════════════════════════════════════════════════════════
   CALENDRIER DE RÉSERVATION
   ──────────────────────────────────────────────────────────────────────────
   Deux données viennent du serveur, posées par index.php :
     • bookedDates  — les jours déjà pris, au format « AAAA-MM-JJ » ;
     • tarifSaisons — les périodes tarifaires, pour annoncer la saison dès que
                      la date d'arrivée est choisie.

   Deux principes ont guidé la réécriture :

   1. Aucune date n'est construite à partir d'une chaîne. « new Date('2026-08-01') »
      est lu en temps universel : selon le fuseau du visiteur, le jour affiché
      n'est pas celui qu'il a cliqué. Tout se calcule ici en année, mois, jour.

   2. Une sélection impossible ne doit pas pouvoir être faite. Dès l'arrivée
      choisie, les jours situés au-delà de la première nuit occupée sont
      désactivés : le visiteur ne peut plus composer un séjour à cheval sur une
      semaine louée, et n'a donc pas de message d'erreur à lire.
   ══════════════════════════════════════════════════════════════════════════ */
(function () {
    var grille = document.getElementById('calMois');
    if (!grille) return;   // page sans calendrier

    var MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    var JOURS_COURTS = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];
    var JOURS = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    var NUITS_MINI = 3;

    var occupes = {};
    (typeof bookedDates !== 'undefined' ? bookedDates : []).forEach(function (j) { occupes[j] = true; });

    var saisons = (typeof tarifSaisons !== 'undefined') ? tarifSaisons : [];

    var titre  = document.getElementById('calTitre');
    var prec   = document.getElementById('calPrec');
    var suiv   = document.getElementById('calSuiv');
    var aide   = document.getElementById('calAide');
    var recap  = document.getElementById('resaRecap');
    var envoi  = document.getElementById('resaEnvoyer');
    var champA = document.getElementById('input_check_in');
    var champD = document.getElementById('input_check_out');

    var aujourdhui = new Date();
    var CLE_AUJ = cle(aujourdhui.getFullYear(), aujourdhui.getMonth(), aujourdhui.getDate());

    var curseur = new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 1);
    var arrivee = null;   // « AAAA-MM-JJ »
    var depart  = null;

    // Deux mois côte à côte dès qu'il y a la place, un seul sinon.
    var deuxMois = window.matchMedia('(min-width: 1280px)');

    // ── Petits utilitaires de date, sans analyse de chaîne ─────────────────
    function cle(a, m, j) {
        return a + '-' + String(m + 1).padStart(2, '0') + '-' + String(j).padStart(2, '0');
    }
    function versDate(c) {
        var p = c.split('-');
        return new Date(+p[0], +p[1] - 1, +p[2]);
    }
    function nuitsEntre(a, b) {
        return Math.round((versDate(b) - versDate(a)) / 86400000);
    }
    function enFrancais(c) {
        var d = versDate(c);
        var j = d.getDate();
        return JOURS[d.getDay()] + ' ' + (j === 1 ? '1er' : j) + ' ' + MOIS[d.getMonth()] + ' ' + d.getFullYear();
    }
    /** « 13 septembre » — sans le jour de la semaine ni l'année. */
    function court(c) {
        var d = versDate(c);
        var j = d.getDate();
        return (j === 1 ? '1er' : j) + ' ' + MOIS[d.getMonth()];
    }
    function euros(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €';
    }

    /** Première nuit occupée à partir d'une date, dans les deux ans à venir. */
    function prochainJourOccupe(depuis) {
        var d = versDate(depuis);
        for (var i = 0; i < 730; i++) {
            d.setDate(d.getDate() + 1);
            var c = cle(d.getFullYear(), d.getMonth(), d.getDate());
            if (occupes[c]) return c;
        }
        return null;
    }

    /** Saison tarifaire contenant une date. */
    function saisonDe(c) {
        for (var i = 0; i < saisons.length; i++) {
            if (saisons[i].debut <= c && c <= saisons[i].fin) return saisons[i];
        }
        return null;
    }

    /** Un jour est-il cliquable dans l'état courant de la sélection ? */
    function selectionnable(c) {
        if (c < CLE_AUJ || occupes[c]) return false;

        // Arrivée choisie, départ à venir : on borne au premier jour occupé.
        if (arrivee && !depart) {
            if (c <= arrivee) return true;   // permet de reprendre l'arrivée
            var butoir = prochainJourOccupe(arrivee);
            return !butoir || c <= butoir;
        }
        return true;
    }

    // ── Rendu ──────────────────────────────────────────────────────────────
    function moisHtml(annee, mois) {
        var premier = new Date(annee, mois, 1);
        var nbJours = new Date(annee, mois + 1, 0).getDate();
        var decalage = (premier.getDay() + 6) % 7;   // la semaine commence lundi

        var h = '<div class="cal-bloc"><p class="cal-nom">' + MOIS[mois] + ' ' + annee + '</p>';

        h += '<div class="cal-semaine">';
        for (var i = 0; i < 7; i++) {
            h += '<span class="cal-jour-nom"><abbr title="' + JOURS[(i + 1) % 7] + '">'
               + JOURS_COURTS[i].charAt(0).toUpperCase() + '</abbr></span>';
        }
        h += '</div><div class="cal-jours">';

        for (var v = 0; v < decalage; v++) h += '<span class="cal-vide"></span>';

        for (var j = 1; j <= nbJours; j++) {
            var c = cle(annee, mois, j);
            var classes = ['cal-jour'];
            var etat = '';

            if (occupes[c]) {
                classes.push('est-occupe');
                etat = ' — déjà réservé';
            } else if (c < CLE_AUJ) {
                classes.push('est-passe');
                etat = ' — date passée';
            } else if (!selectionnable(c)) {
                classes.push('est-hors-portee');
                etat = ' — indisponible pour ce séjour';
            }

            if (c === arrivee) { classes.push('est-choisi', 'est-arrivee'); etat = ' — votre arrivée'; }
            if (c === depart)  { classes.push('est-choisi', 'est-depart');  etat = ' — votre départ'; }
            if (arrivee && depart && c > arrivee && c < depart) classes.push('est-entre');

            var inactif = classes.indexOf('est-occupe') > -1
                       || classes.indexOf('est-passe') > -1
                       || classes.indexOf('est-hors-portee') > -1;

            h += '<button type="button" class="' + classes.join(' ') + '" data-jour="' + c + '"'
               + (inactif ? ' disabled' : '')
               + ' aria-label="' + enFrancais(c) + etat + '">' + j + '</button>';
        }

        return h + '</div></div>';
    }

    function dessiner() {
        var a = curseur.getFullYear(), m = curseur.getMonth();
        var html = moisHtml(a, m);

        if (deuxMois.matches) {
            var suivant = new Date(a, m + 1, 1);
            html += moisHtml(suivant.getFullYear(), suivant.getMonth());
        }
        grille.innerHTML = html;

        // Le titre sert de repère aux lecteurs d'écran ; les mois sont déjà
        // nommés au-dessus de chaque bloc.
        var fin = new Date(a, m + (deuxMois.matches ? 1 : 0), 1);
        titre.textContent = deuxMois.matches
            ? MOIS[m] + ' – ' + MOIS[fin.getMonth()] + ' ' + fin.getFullYear()
            : MOIS[m] + ' ' + a;

        // On ne remonte pas avant le mois en cours.
        prec.disabled = (a === aujourdhui.getFullYear() && m === aujourdhui.getMonth());

        majAide();
    }

    function majAide() {
        if (!aide) return;

        if (arrivee && depart) {
            aide.textContent = '';
        } else if (arrivee) {
            var butoir = prochainJourOccupe(arrivee);
            aide.textContent = butoir
                ? 'Choisissez la date de départ — la villa est reprise le ' + court(butoir) + '.'
                : 'Choisissez maintenant la date de départ.';
        } else {
            aide.textContent = '';
        }
    }

    // ── Récapitulatif et intitulé du bouton ────────────────────────────────
    function majRecap() {
        champA.value = arrivee || '';
        champD.value = (arrivee && depart) ? depart : '';

        if (arrivee && depart) {
            var nuits = nuitsEntre(arrivee, depart);
            var saison = saisonDe(arrivee);

            var h = '<p class="resa-recap-titre">' + nuits + ' nuits</p>'
                  + '<dl class="resa-recap-liste">'
                  + '<div><dt>Arrivée</dt><dd>' + enFrancais(arrivee) + '</dd></div>'
                  + '<div><dt>Départ</dt><dd>' + enFrancais(depart) + '</dd></div>';

            if (saison && saison.prix > 0) {
                h += '<div><dt>' + saison.nom + '</dt><dd>' + euros(saison.prix) + ' la semaine</dd></div>';
            }

            h += '</dl><p class="resa-recap-note">Tarif exact confirmé par nos soins sous 24 heures.</p>'
               + '<button type="button" class="resa-effacer" id="resaEffacer">Modifier mes dates</button>';

            recap.className = 'resa-recap est-rempli';
            recap.innerHTML = h;
            document.getElementById('resaEffacer').addEventListener('click', effacer);

            envoi.textContent = 'Demander cette période';
        } else if (arrivee) {
            recap.className = 'resa-recap est-partiel';
            recap.innerHTML = '<p class="resa-recap-titre">Arrivée le ' + enFrancais(arrivee) + '</p>'
                            + '<p class="resa-recap-note">Sélectionnez la date de départ.</p>'
                            + '<button type="button" class="resa-effacer" id="resaEffacer">Annuler</button>';
            document.getElementById('resaEffacer').addEventListener('click', effacer);

            envoi.textContent = 'Envoyer ma demande';
        } else {
            recap.className = 'resa-recap';
            recap.innerHTML = '<p class="resa-recap-vide">Aucune date sélectionnée — nous répondrons à vos questions.</p>';
            envoi.textContent = 'Envoyer ma demande';
        }
    }

    function effacer() {
        arrivee = depart = null;
        dessiner();
        majRecap();
    }

    // ── Sélection ──────────────────────────────────────────────────────────
    function choisir(c) {
        if (!arrivee || depart) {
            arrivee = c;
            depart = null;
        } else if (c <= arrivee) {
            arrivee = c;            // le visiteur revient en arrière
        } else if (nuitsEntre(arrivee, c) < NUITS_MINI) {
            if (typeof showNotification === 'function') {
                showNotification('Le séjour est de ' + NUITS_MINI + ' nuits minimum.', 'error');
            }
            return;
        } else {
            depart = c;
        }

        dessiner();
        majRecap();
    }

    grille.addEventListener('click', function (e) {
        var bouton = e.target.closest ? e.target.closest('.cal-jour') : null;
        if (bouton && !bouton.disabled) choisir(bouton.getAttribute('data-jour'));
    });

    prec.addEventListener('click', function () {
        curseur.setMonth(curseur.getMonth() - 1);
        dessiner();
    });
    suiv.addEventListener('click', function () {
        curseur.setMonth(curseur.getMonth() + 1);
        dessiner();
    });

    // Le passage d'un à deux mois suit la largeur de la fenêtre.
    if (deuxMois.addEventListener) {
        deuxMois.addEventListener('change', dessiner);
    } else if (deuxMois.addListener) {
        deuxMois.addListener(dessiner);   // Safari ancien
    }

    dessiner();
    majRecap();
})();

/* ══════════════════════════════════════════════════════════════════════════
   ENVOI DU FORMULAIRE DE RÉSERVATION
   Les champs se valident à la soumission, et non par une fonction appelée
   depuis l'attribut du bouton : la touche Entrée déclenche donc le même
   contrôle que le clic.
   ══════════════════════════════════════════════════════════════════════════ */
(function () {
    var form = document.getElementById('bookingForm');
    if (!form) return;

    var bouton = document.getElementById('resaEnvoyer');
    var envoiEnCours = false;

    function erreur(champ, message) {
        if (typeof showNotification === 'function') showNotification(message, 'error');
        if (champ) {
            champ.classList.add('est-invalide');
            champ.focus();
            champ.addEventListener('input', function retirer() {
                champ.classList.remove('est-invalide');
                champ.removeEventListener('input', retirer);
            });
        }
        return false;
    }

    form.addEventListener('submit', function (e) {
        if (envoiEnCours) { e.preventDefault(); return; }

        var nom   = form.querySelector('[name="customer_name"]');
        var email = form.querySelector('[name="customer_email"]');
        var tel   = form.querySelector('[name="customer_phone"]');

        var valide = true;
        if (!nom.value.trim() || nom.value.trim().length < 2) {
            valide = erreur(nom, 'Merci d’indiquer votre nom.');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value.trim())) {
            valide = erreur(email, 'Cette adresse e-mail ne semble pas valide.');
        } else if (tel.value.replace(/[^0-9]/g, '').length < 9) {
            valide = erreur(tel, 'Merci d’indiquer un numéro de téléphone.');
        }

        if (!valide) { e.preventDefault(); return; }

        // Retour visuel immédiat, et garde-fou contre le double envoi.
        envoiEnCours = true;
        bouton.disabled = true;
        bouton.classList.add('est-en-cours');
        bouton.textContent = 'Envoi en cours…';
    });
})();

/* ══════════════════════════════════════════════════════════════
   LOGIQUE DE LA PAGE D'ACCUEIL (index.php)
   ══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    /* --- Modale Contact --- */
    window.openContactModal = function () {
        var m = document.getElementById('contactModal');
        if (m) {
            m.classList.add('active');
            document.body.style.overflow = 'hidden';
            var btn = m.querySelector('.contact-close-btn');
            if (btn) setTimeout(function(){ btn.focus(); }, 350);
        }
    };

    window.closeContactModal = function () {
        var m = document.getElementById('contactModal');
        if (m) {
            m.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') closeContactModal();
    });

    // Fermeture modale au clic sur l'overlay
    var modal = document.getElementById('contactModal');
    if (modal) {
        modal.addEventListener('click', function(e){
            if (e.target === modal) {
                closeContactModal();
            }
        });
        
        // Swipe down sur mobile (Bottom Sheet)
        var startY = 0, currentY = 0;
        var contactCard = modal.querySelector('.contact-card');
        
        if (contactCard) {
            contactCard.addEventListener('touchstart', function(e) {
                if (window.innerWidth >= 600) return; 
                startY = e.touches[0].clientY;
                contactCard.style.transition = 'none';
            }, {passive:true});
            
            contactCard.addEventListener('touchmove', function(e) {
                if (window.innerWidth >= 600) return;
                currentY = e.touches[0].clientY;
                var diff = currentY - startY;
                if (diff > 0) {
                    contactCard.style.transform = 'translateY(' + diff + 'px)';
                }
            }, {passive:true});
            
            contactCard.addEventListener('touchend', function(e) {
                if (window.innerWidth >= 600) return;
                contactCard.style.transition = 'transform .35s cubic-bezier(.32,1,.28,1)';
                var diff = currentY - startY;
                if (diff > 100) {
                    closeContactModal();
                    setTimeout(function(){ contactCard.style.transform = ''; }, 350);
                } else {
                    contactCard.style.transform = 'translateY(0)';
                }
            });
        }
    }

    /* --- Vidéo Hero --- */
    function initVideo() {
        var video  = document.getElementById('heroVideo');
        var poster = document.getElementById('heroPoster');
        if (!video) return;

        function playVideo() {
            var playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.then(function () {
                    video.style.opacity  = '1';
                    if (poster) poster.style.opacity = '0';
                }).catch(function () { /* autoplay bloqué */ });
            }
        }

        video.src = 'Video/bellevuedaveyron.mp4';
        video.load();

        if (video.readyState >= 3) {
            playVideo();
        } else {
            video.addEventListener('canplaythrough', playVideo);
        }
    }

    /* --- Intro Animation --- */
    function startIntro() {
        var overlay     = document.getElementById('intro-overlay');
        var sm          = document.getElementById('successModal');
        var isSuccess   = sm && sm.classList.contains('active');
        var introPlayed = sessionStorage.getItem('introPlayed');
        var hasHash     = window.location.hash.length > 1;

        document.body.classList.remove('loading');

        function triggerVideo() {
            if (document.readyState === 'complete') {
                initVideo();
            } else {
                window.addEventListener('load', initVideo);
            }
        }

        if (!overlay) { triggerVideo(); return; }

        if (isSuccess || introPlayed || hasHash) {
            overlay.style.display = 'none';
            if (isSuccess && history.replaceState) history.replaceState(null, null, window.location.pathname);
            triggerVideo();
        } else {
            overlay.classList.add('animate');
            setTimeout(function () {
                overlay.classList.add('hidden');
                sessionStorage.setItem('introPlayed', 'true');
                triggerVideo();
            }, 3000);
        }
    }

    /* --- Slideshows (PMR, Family) --- */
    function initSlideshow(sel, ms) {
        var slides = document.querySelectorAll(sel);
        if (slides.length <= 1) return;
        var idx = 0;
        setInterval(function () {
            slides[idx].classList.remove('active');
            idx = (idx + 1) % slides.length;
            slides[idx].classList.add('active');
        }, ms);
    }

    function initHome() {
        startIntro();
        initSlideshow('.pmr-slide',    4500);
        initSlideshow('.family-slide', 5000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initHome);
    } else {
        initHome();
    }

})();

/* ══════════════════════════════════════════════════════════════
   LOGIQUE DE LA PAGE DÉCOUVRIR (decouvrir.php)
   (Fusionné de decouvrir.js)
   ══════════════════════════════════════════════════════════════ */

(function () {
    "use strict";

    // Flip cards en mode exclusif
    window.flipExclusive = function (card) {
        var isFlipped = card.classList.contains("flipped");
        var row = card.closest(".dist-cards-row");
        if (row) {
            row.querySelectorAll(".flip-card").forEach(function (c) {
                c.classList.remove("flipped");
            });
        }
        if (!isFlipped) card.classList.add("flipped");
    };

    // Onglets Agenda
    window.activateTab = function (tabId, btnEl) {
        document.querySelectorAll(".tab-panel").forEach(function (p) { p.classList.remove("active"); });
        var target = document.getElementById("panel-" + tabId);
        if (target) target.classList.add("active");

        document.querySelectorAll(".tab-btn").forEach(function (b) {
            b.classList.remove("active");
            b.setAttribute("aria-selected", "false");
        });
        
        var activeBtn = btnEl || document.querySelector('.tab-btn[data-tab="' + tabId + '"]');
        if (activeBtn) {
            activeBtn.classList.add("active");
            activeBtn.setAttribute("aria-selected", "true");
        }

        if (!btnEl) {
            var section = document.getElementById("agenda");
            if (section) setTimeout(function () { section.scrollIntoView({ behavior: "smooth", block: "start" }); }, 80);
        }
    };

    // Onglets Coups de Cœur
    document.querySelectorAll(".coups-tab-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var targetId = this.dataset.target;
            document.querySelectorAll(".coups-tab-btn").forEach(function (b) { b.classList.remove("active"); });
            document.querySelectorAll(".coups-panel").forEach(function (p) { p.classList.remove("active"); });

            this.classList.add("active");
            var panel = document.getElementById(targetId);
            if (panel) panel.classList.add("active");
        });
    });

    // Skeleton Iframes
    window.hideSkeleton = function (id) {
        var skel = document.getElementById(id);
        if (skel) skel.classList.add("hidden");
    };

    // Header scrolly
    var navbar = document.getElementById("navbar");
    if (navbar) {
        window.addEventListener("scroll", function () {
            navbar.classList.toggle("scrolled", window.scrollY > 60);
        }, { passive: true });
    }

    // Toggle menu
    window.toggleMenu = function () {
        var nav = document.getElementById("navLinks");
        if (nav) nav.classList.toggle("active");
    };

    document.addEventListener("click", function (e) {
        var nav = document.getElementById("navLinks");
        var toggle = document.querySelector(".menu-toggle");
        if (nav && nav.classList.contains("active")) {
            if (!nav.contains(e.target) && toggle && !toggle.contains(e.target)) {
                nav.classList.remove("active");
            }
        }
    });

    // Hash navigation
    (function handleInboundHash() {
        var hash = window.location.hash;
        if (!hash) return;
        var hashMap = {
            "#agenda": "agenda", "#distances": "distances",
            "#coups-tables": "coups-tables", "#coups-culture": "coups-culture",
            "#coups-nature": "coups-nature", "#coups-artisanat": "coups-artisanat"
        };
        var targetId = hashMap[hash];
        if (targetId) {
            setTimeout(function () {
                var el = document.getElementById(targetId);
                if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
            }, 300);
        }
    })();

})();

/* ══════════════════════════════════════════════════════════════════════════
   CHIFFRES CLÉS — le compteur
   Les valeurs de la bande sous le bandeau défilent de zéro jusqu'à leur
   valeur réelle, une fois seulement, au moment où la bande entre dans
   l'écran. Le HTML contient déjà la valeur finale : sans JavaScript, ou si
   le visiteur a demandé moins d'animations, elle s'affiche telle quelle.
   ══════════════════════════════════════════════════════════════════════════ */
(function () {
    var nombres = document.querySelectorAll('.chiffre-nombre[data-compteur]');
    if (!nombres.length) return;

    var sobre = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (sobre || !('IntersectionObserver' in window)) return;

    var DUREE = 1400;   // millisecondes

    function formater(valeur) {
        // Espace insécable fine pour les milliers, comme number_format côté PHP.
        return String(valeur).replace(/\B(?=(\d{3})+(?!\d))/g, '\u202F');
    }

    function animer(element) {
        var cible = parseInt(element.getAttribute('data-compteur'), 10) || 0;
        var ligne = element.closest ? element.closest('.chiffre-valeur') : null;

        if (ligne) {
            ligne.classList.remove('compteur-pret');
            ligne.classList.add('compteur-en-cours');
        }

        // Zéro n'a rien à faire défiler : la ligne se pose, sans décompte.
        if (cible === 0) {
            if (ligne) ligne.classList.add('compteur-fini');
            return;
        }

        var debut = null;

        function pas(horodatage) {
            if (debut === null) debut = horodatage;

            var avancement = Math.min((horodatage - debut) / DUREE, 1);
            // Décélération : le nombre s'élance puis se pose doucement.
            var douceur = 1 - Math.pow(1 - avancement, 3);

            element.textContent = formater(Math.round(cible * douceur));

            if (avancement < 1) {
                requestAnimationFrame(pas);
            } else {
                element.textContent = formater(cible);
                if (ligne) {
                    ligne.classList.remove('compteur-en-cours');
                    ligne.classList.add('compteur-fini');
                }
            }
        }

        requestAnimationFrame(pas);
    }

    // État de départ posé par le script, jamais dans le HTML : une page sans
    // JavaScript ne doit pas rester avec des valeurs à zéro.
    Array.prototype.forEach.call(nombres, function (element) {
        var ligne = element.parentNode;
        if (ligne && ligne.classList) ligne.classList.add('compteur-pret');
        element.textContent = '0';
    });

    var observateur = new IntersectionObserver(function (entrees) {
        entrees.forEach(function (entree) {
            if (!entree.isIntersecting) return;
            observateur.unobserve(entree.target);
            animer(entree.target);
        });
    }, { threshold: 0.4 });

    Array.prototype.forEach.call(nombres, function (element) {
        observateur.observe(element);
    });
})();
