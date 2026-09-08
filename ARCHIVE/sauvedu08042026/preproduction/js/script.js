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

// --- 1. GESTION DE l'INTRODUCTION ---
window.addEventListener('load', () => {
    const overlay = document.getElementById('intro-overlay');
    const body = document.body;
    const successModal = document.getElementById('successModal');
    const isReservationSuccess = successModal && successModal.classList.contains('active');
    const introPlayed = sessionStorage.getItem('introPlayed');

    if(overlay) {
        if (isReservationSuccess || introPlayed) {
            overlay.style.display = 'none'; 
            body.classList.remove('loading'); 
            if (isReservationSuccess && history.replaceState) {
                history.replaceState(null, null, window.location.pathname);
            }
        } else {
            overlay.classList.add('animate');
            setTimeout(() => {
                overlay.classList.add('hidden');
                body.classList.remove('loading');
                sessionStorage.setItem('introPlayed', 'true');
            }, 5000);
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

document.addEventListener('DOMContentLoaded', renderCalendar);

/* --- 4. DIAPORAMA ACCESSIBILITÉ (PMR) --- */
function initPmrSlideshow() {
    const slides = document.querySelectorAll('.pmr-slide');
    if (slides.length <= 1) return; // Pas besoin d'animation si 0 ou 1 image

    let currentIndex = 0;

    setInterval(() => {
        // On enlève la classe active de l'image courante
        slides[currentIndex].classList.remove('active');

        // On passe à la suivante (boucle)
        currentIndex = (currentIndex + 1) % slides.length;

        // On ajoute la classe active à la nouvelle image
        slides[currentIndex].classList.add('active');
    }, 4500); // Changement toutes les 4.5 secondes (temps de lecture relaxant)
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', () => {
    initPmrSlideshow();
    
    // --- 5. VALIDATION DU FORMULAIRE DE RÉSERVATION ---
    window.validateBooking = function() {
        const form = document.getElementById('bookingForm');
        if (!form) return;

        const checkIn = document.getElementById('input_check_in').value;
        const checkOut = document.getElementById('input_check_out').value;
        const name = form.querySelector('[name="customer_name"]').value.trim();
        const email = form.querySelector('[name="customer_email"]').value.trim();
        const phone = form.querySelector('[name="customer_phone"]').value.trim();
        
        let errors = [];
        
        // Validation des coordonnées (toujours obligatoires)
        if (!name) errors.push("Le nom est obligatoire.");
        if (!email) errors.push("L'adresse e-mail est obligatoire.");
        if (!phone) errors.push("Le téléphone est obligatoire.");
        
        if (errors.length > 0) {
            errors.forEach((err, index) => {
                setTimeout(() => showNotification(err, "error"), index * 200);
            });
            return;
        }

        // Gestion des dates
        if (!checkIn || !checkOut) {
            // Au lieu du confirm(), on ouvre la modale personnalisée
            document.getElementById('dateConfirmModal').classList.add('active');
        } else if (!checkMinStay(checkIn, checkOut)) {
            showNotification("Le séjour doit être de 3 nuits minimum pour une réservation ferme.", "error");
        } else {
            // Tout est OK : envoi direct
            form.submit();
        }
    };
});

