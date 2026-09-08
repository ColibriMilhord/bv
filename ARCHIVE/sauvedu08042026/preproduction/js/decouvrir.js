/**
 * decouvrir.js — Interactions de la page Découvrir la Région
 * Bellevue d'Aveyron ★★★★★
 *
 * Fonctions :
 *  - flipExclusive()   : flip cards en mode exclusif (une seule ouverte à la fois)
 *  - activateTab()     : onglets Agenda + scroll vers la section
 *  - coups-tab-btn     : onglets Coups de Cœur
 *  - scroll navbar     : classe .scrolled sur le header
 *  - toggleMenu()      : menu hamburger mobile
 *  - hideSkeleton()    : masque le skeleton après chargement iframe
 *  - anchorFromIndex() : scroll auto si on arrive depuis index.php avec un hash
 */

(function () {
    "use strict";

    /* ─────────────────────────────────────────────────────
       FLIP CARDS — mode exclusif par groupe (dist-cards-row)
       Une seule carte retournée à la fois dans chaque rangée.
    ───────────────────────────────────────────────────── */
    window.flipExclusive = function (card) {
        var isFlipped = card.classList.contains("flipped");

        // Refermer toutes les cartes du même groupe
        var row = card.closest(".dist-cards-row");
        if (row) {
            row.querySelectorAll(".flip-card").forEach(function (c) {
                c.classList.remove("flipped");
            });
        }

        // Ouvrir la carte cliquée (sauf si elle était déjà ouverte → toggle)
        if (!isFlipped) {
            card.classList.add("flipped");
        }
    };


    /* ─────────────────────────────────────────────────────
       ONGLETS AGENDA
    ───────────────────────────────────────────────────── */
    var currentTab = "autour";

    window.activateTab = function (tabId, btnEl) {
        // Masquer tous les panels
        document.querySelectorAll(".tab-panel").forEach(function (p) {
            p.classList.remove("active");
        });

        // Afficher le panel cible
        var target = document.getElementById("panel-" + tabId);
        if (target) target.classList.add("active");

        // Mise à jour des boutons
        document.querySelectorAll(".tab-btn").forEach(function (b) {
            b.classList.remove("active");
            b.setAttribute("aria-selected", "false");
        });
        var activeBtn = btnEl || document.querySelector('.tab-btn[data-tab="' + tabId + '"]');
        if (activeBtn) {
            activeBtn.classList.add("active");
            activeBtn.setAttribute("aria-selected", "true");
        }

        currentTab = tabId;

        // Scroll vers la section Agenda (uniquement si déclenché par une carte thème)
        if (!btnEl) {
            var section = document.getElementById("agenda");
            if (section) {
                setTimeout(function () {
                    section.scrollIntoView({ behavior: "smooth", block: "start" });
                }, 80);
            }
        }
    };


    /* ─────────────────────────────────────────────────────
       ONGLETS COUPS DE CŒUR
    ───────────────────────────────────────────────────── */
    document.querySelectorAll(".coups-tab-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            var targetId = this.dataset.target;

            document.querySelectorAll(".coups-tab-btn").forEach(function (b) {
                b.classList.remove("active");
            });
            document.querySelectorAll(".coups-panel").forEach(function (p) {
                p.classList.remove("active");
            });

            this.classList.add("active");
            var panel = document.getElementById(targetId);
            if (panel) panel.classList.add("active");
        });
    });


    /* ─────────────────────────────────────────────────────
       SKELETON IFRAMES
    ───────────────────────────────────────────────────── */
    window.hideSkeleton = function (id) {
        var skel = document.getElementById(id);
        if (skel) skel.classList.add("hidden");
    };


    /* ─────────────────────────────────────────────────────
       HEADER — classe .scrolled au défilement
    ───────────────────────────────────────────────────── */
    var navbar = document.getElementById("navbar");
    if (navbar) {
        window.addEventListener("scroll", function () {
            navbar.classList.toggle("scrolled", window.scrollY > 60);
        }, { passive: true });
    }


    /* ─────────────────────────────────────────────────────
       MENU MOBILE — hamburger toggle
    ───────────────────────────────────────────────────── */
    window.toggleMenu = function () {
        var nav = document.getElementById("navLinks");
        if (nav) nav.classList.toggle("active");
    };

    // Fermer le menu si on clique en dehors
    document.addEventListener("click", function (e) {
        var nav = document.getElementById("navLinks");
        var toggle = document.querySelector(".menu-toggle");
        if (nav && nav.classList.contains("active")) {
            if (!nav.contains(e.target) && toggle && !toggle.contains(e.target)) {
                nav.classList.remove("active");
            }
        }
    });


    /* ─────────────────────────────────────────────────────
       NAVIGATION DEPUIS INDEX.PHP
       Si on arrive sur decouvrir.php depuis un lien de index.php
       contenant un hash (#agenda, #coups-tables…), on scroll
       automatiquement vers la bonne section.
    ───────────────────────────────────────────────────── */
    (function handleInboundHash() {
        var hash = window.location.hash;
        if (!hash) return;

        // Correspondance hash → id de section sur cette page
        var hashMap = {
            "#agenda":        "agenda",
            "#distances":     "distances",
            "#coups-tables":  "coups-tables",
            "#coups-culture": "coups-culture",
            "#coups-nature":  "coups-nature",
            "#coups-artisanat": "coups-artisanat"
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
