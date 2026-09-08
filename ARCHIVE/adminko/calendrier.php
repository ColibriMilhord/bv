<?php
require_once 'includes/header.php';
?>

<div class="flex justify-between items-center mb-4">
    <h1>Calendrier des disponibilités</h1>
    <div class="flex gap-4">
        <button class="btn btn-secondary" id="prevMonth"><i class="fas fa-chevron-left"></i> Mois Précédent</button>
        <h2 id="currentMonthYear" style="margin: 0; align-self: center;">Mois Année</h2>
        <button class="btn btn-secondary" id="nextMonth">Mois Suivant <i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<div class="glass-panel">
    <div class="calendar-wrapper">
        <div class="calendar-grid">
            <div class="cal-day-header">Lun</div>
            <div class="cal-day-header">Mar</div>
            <div class="cal-day-header">Mer</div>
            <div class="cal-day-header">Jeu</div>
            <div class="cal-day-header">Ven</div>
            <div class="cal-day-header">Sam</div>
            <div class="cal-day-header">Dim</div>
            
            <!-- Calendar days constructed by JS -->
            <div id="calendarDays" style="display: contents;"></div>
        </div>
    </div>
</div>

<!-- Reservation Details Modal -->
<div class="modal-overlay" id="resModal">
    <div class="glass-panel modal-content">
        <div class="modal-header">
            <h3>Détail de réservation</h3>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body" id="resModalBody">
            <!-- Modal Content dynamically injected -->
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
