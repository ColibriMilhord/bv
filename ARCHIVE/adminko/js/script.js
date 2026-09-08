document.addEventListener('DOMContentLoaded', function() {
    // Global State for Calendar
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let events = [];

    const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];

    // Navigation buttons
    const prevBtn = document.getElementById('prevMonth');
    const nextBtn = document.getElementById('nextMonth');
    const monthYearDisplay = document.getElementById('currentMonthYear');
    const calendarDays = document.getElementById('calendarDays');

    if (calendarDays) {
        initCalendar();
    }

    function initCalendar() {
        // Fetch events from API
        fetch('api.php?action=get_events')
            .then(response => response.json())
            .then(data => {
                events = data;
                renderCalendar();
            })
            .catch(error => {
                console.error('Erreur API:', error);
                renderCalendar(); // Render anyway
            });
            
        prevBtn.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar();
        });

        nextBtn.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar();
        });
        
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('resModal').classList.remove('active');
        });
    }

    function renderCalendar() {
        calendarDays.innerHTML = '';
        monthYearDisplay.textContent = `${monthNames[currentMonth]} ${currentYear}`;

        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        
        // JS getDay() starts Sunday=0, we want Monday=0
        let startDay = firstDay - 1;
        if (startDay === -1) { startDay = 6; } // Sunday

        // Empty cells before start
        for (let i = 0; i < startDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'cal-day empty';
            calendarDays.appendChild(emptyCell);
        }

        const today = new Date();

        for (let i = 1; i <= daysInMonth; i++) {
            const cellDate = new Date(Date.UTC(currentYear, currentMonth, i));
            const dateStr = cellDate.toISOString().split('T')[0];
            
            const cell = document.createElement('div');
            cell.className = 'cal-day';
            
            if (currentYear === today.getFullYear() && currentMonth === today.getMonth() && i === today.getDate()) {
                cell.classList.add('today');
            }

            const numSpan = document.createElement('div');
            numSpan.className = 'date-num';
            numSpan.textContent = i;
            cell.appendChild(numSpan);

            // Add events
            let dayStatus = 'libre'; // default
            let relatedEvent = null;

            events.forEach(evt => {
                const start = new Date(evt.start);
                const end = new Date(evt.end);
                const current = new Date(currentYear, currentMonth, i);
                
                // Compare times correctly
                if (current >= start && current < end) {
                    dayStatus = 'reserve';
                    relatedEvent = evt;
                }
            });

            cell.classList.add(`status-${dayStatus}`);
            
            if (relatedEvent) {
                const titleSpan = document.createElement('div');
                titleSpan.style.fontSize = '0.75rem';
                titleSpan.style.color = 'var(--text-secondary)';
                titleSpan.style.marginTop = '4px';
                titleSpan.style.whiteSpace = 'nowrap';
                titleSpan.style.overflow = 'hidden';
                titleSpan.style.textOverflow = 'ellipsis';
                titleSpan.textContent = relatedEvent.title;
                cell.appendChild(titleSpan);
                
                cell.addEventListener('click', () => {
                    openModal(relatedEvent);
                });
            }

            calendarDays.appendChild(cell);
        }
    }
    
    function openModal(evt) {
        document.getElementById('resModalBody').innerHTML = `
            <p><strong>Client :</strong> ${evt.title}</p>
            <p><strong>Arrivée :</strong> ${new Date(evt.start).toLocaleDateString('fr-FR')}</p>
            <p><strong>Départ :</strong> ${new Date(evt.end).toLocaleDateString('fr-FR')}</p>
            <p><span class="badge validee">Confirmée</span></p>
        `;
        document.getElementById('resModal').classList.add('active');
    }
});
