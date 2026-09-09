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

// --- 3. CALENDRIER INTELLIGENT ---
// 'bookedDates' est défini dans index.php
let currentDate = new Date();
let selectedStart = null;
let selectedEnd = null;

function renderCalendar() {
    // ... (Code de rendu du calendrier identique au précédent) ...
    // Je remets le début pour la structure, le cœur ne change pas
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
    const titleEl = document.getElementById('calendarTitle');
    if(titleEl) titleEl.innerText = `${monthNames[month]} ${year}`;
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    let startDayIndex = firstDay.getDay() - 1; 
    if (startDayIndex === -1) startDayIndex = 6;
    const grid = document.getElementById('calendarDays');
    if(!grid) return;
    grid.innerHTML = '';
    for (let i = 0; i < startDayIndex; i++) grid.innerHTML += `<div></div>`;

    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const el = document.createElement('div');
        el.className = 'day-cell';
        el.innerText = day;
        
        const isBooked = typeof bookedDates !== 'undefined' && bookedDates.includes(dateStr);
        const isPast = new Date(dateStr) < new Date().setHours(0,0,0,0);

        if (isBooked || isPast) {
            el.classList.add('disabled');
        } else {
            el.onclick = () => selectDate(dateStr);
        }

        if (selectedStart === dateStr) { el.classList.add('selected', 'range-start'); }
        if (selectedEnd === dateStr) { el.classList.add('selected', 'range-end'); }
        if (selectedStart && selectedEnd && dateStr > selectedStart && dateStr < selectedEnd) {
            el.classList.add('range');
        }
        grid.appendChild(el);
    }
}

function selectDate(dateStr) {
    if (!selectedStart || (selectedStart && selectedEnd)) {
        // Clic 1 : Début
        selectedStart = dateStr;
        selectedEnd = null;
    } else if (dateStr < selectedStart) {
        // Correction si clic avant
        selectedStart = dateStr;
    } else {
        // Clic 2 : Fin -> C'EST ICI QU'ON VÉRIFIE LES RÈGLES
        
        // 1. Vérif Disponibilité
        if(!checkAvailability(selectedStart, dateStr)) {
            showNotification("Certaines dates sélectionnées sont indisponibles.", "error");
            selectedStart = dateStr; // Reset
            selectedEnd = null;
        } 
        // 2. Vérif Durée (3 nuits min)
        else if (!checkMinStay(selectedStart, dateStr)) {
            showNotification("Le séjour doit être de 3 nuits minimum.", "error");
            // On ne valide pas la fin, on laisse l'utilisateur choisir une autre date
        } 
        else {
            selectedEnd = dateStr;
        }
    }
    renderCalendar();
    updateForm();
}

function checkMinStay(start, end) {
    const d1 = new Date(start);
    const d2 = new Date(end);
    // Calcul de la différence en jours
    const diffTime = Math.abs(d2 - d1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    return diffDays >= 3;
}

function checkAvailability(start, end) {
    let curr = new Date(start);
    let last = new Date(end);
    while(curr <= last) {
        if(typeof bookedDates !== 'undefined' && bookedDates.includes(curr.toISOString().split('T')[0])) return false;
        curr.setDate(curr.getDate() + 1);
    }
    return true;
}

function updateForm() {
    const inputStart = document.getElementById('input_check_in');
    const inputEnd = document.getElementById('input_check_out');
    
    if(inputStart) inputStart.value = selectedStart || '';
    if(inputEnd) inputEnd.value = selectedEnd || '';
    
    const summary = document.getElementById('bookingSummary');
    if (selectedStart && selectedEnd) {
        // Recalcul pour affichage
        const d1 = new Date(selectedStart);
        const d2 = new Date(selectedEnd);
        const nights = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));

        summary.innerHTML = `
            <strong>Séjour de ${nights} Nuits :</strong><br>
            Du ${d1.toLocaleDateString('fr-FR')}<br>
            Au ${d2.toLocaleDateString('fr-FR')}
        `;
    } else if (selectedStart) {
        summary.innerHTML = `Arrivée : ${new Date(selectedStart).toLocaleDateString('fr-FR')}<br>Sélectionnez la date de départ...`;
    } else {
        summary.innerHTML = "Veuillez sélectionner vos dates.";
    }
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderCalendar);
} else {
    renderCalendar();
}

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

/* ══════════════════════════════════════════════════════════════
   VALIDATION ET SOUMISSION DU FORMULAIRE DE RÉSERVATION
   ══════════════════════════════════════════════════════════════ */

/**
 * Appelée par le bouton "Envoyer la demande".
 * Valide les champs obligatoires puis soumet ou affiche la modale sans-dates.
 */
function setButtonLoading() {
    var btn = document.querySelector('#bookingForm button[onclick="validateBooking()"]');
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:middle;margin-right:8px;"></span>Envoi en cours…';
    // Assure que l'animation CSS "spin" existe
    if (!document.getElementById('spin-style')) {
        var s = document.createElement('style');
        s.id = 'spin-style';
        s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(s);
    }
}

function validateBooking() {
    var form = document.getElementById('bookingForm');
    if (!form) return;

    var name  = form.querySelector('[name="customer_name"]');
    var email = form.querySelector('[name="customer_email"]');
    var phone = form.querySelector('[name="customer_phone"]');

    if (!name || !name.value.trim()) {
        showNotification('Veuillez indiquer votre nom complet.', 'error');
        if (name) name.focus();
        return;
    }
    if (!email || !email.value.trim() || !email.value.includes('@')) {
        showNotification('Veuillez indiquer une adresse e-mail valide.', 'error');
        if (email) email.focus();
        return;
    }
    if (!phone || !phone.value.trim()) {
        showNotification('Veuillez indiquer votre numéro de téléphone.', 'error');
        if (phone) phone.focus();
        return;
    }

    var checkIn  = document.getElementById('input_check_in');
    var checkOut = document.getElementById('input_check_out');

    if (!checkIn || !checkIn.value || !checkOut || !checkOut.value) {
        var modal = document.getElementById('dateConfirmModal');
        if (modal) modal.classList.add('active');
        return;
    }

    // ✅ Retour visuel immédiat avant la soumission
    setButtonLoading();
    form.submit();
}

function submitWithoutDates() {
    var modal = document.getElementById('dateConfirmModal');
    if (modal) modal.classList.remove('active');

    // ✅ Retour visuel immédiat avant la soumission
    setButtonLoading();

    var form = document.getElementById('bookingForm');
    if (form) form.submit();
}


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
