<?php
// iframe_calendar.php
require_once 'config/db.php';

// --- Handling Parameters ---
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// Adjust if month is out of bounds
if ($month > 12) {
    $month = 1;
    $year++;
} else if ($month < 1) {
    $month = 12;
    $year--;
}

// Navigation Links
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// --- Fetch Data ---

// 1. Fetch Reservations for the 4-month window
$start_date = "$year-$month-01";
$end_date_dt = new DateTime("$year-$month-01");
$end_date_dt->modify('+4 months');
$end_date = $end_date_dt->format('Y-m-d');

$stmt = $pdo->prepare("
    SELECT date_debut, date_fin, statut 
    FROM reservations 
    WHERE (statut = 'validee' OR statut = 'attente')
    AND (
        (date_debut BETWEEN ? AND ?) OR 
        (date_fin BETWEEN ? AND ?) OR
        (date_debut <= ? AND date_fin >= ?)
    )
");
$stmt->execute([$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper to check if a date is reserved
function getDayStatus($date, $reservations)
{
    foreach ($reservations as $res) {
        if ($date >= $res['date_debut'] && $date < $res['date_fin']) {
            return ($res['statut'] == 'attente') ? 'reserved' : 'reserved'; // Both red for simplicity or distinguish if needed
        }
    }
    return 'free';
}

// 2. Fetch Pricing Seasons
$stmt_prices = $pdo->query("SELECT * FROM tarifs_saison ORDER BY date_debut ASC");
$seasons = $stmt_prices->fetchAll(PDO::FETCH_ASSOC);

// Helper for formatting price dates
function formatDateFr($date_str)
{
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $formatter->setPattern('dd MMMM');
    return $formatter->format(new DateTime($date_str));
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier Disponibilités</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2,
        h3,
        .serif {
            font-family: 'Playfair Display', serif;
        }

        .cal-day {
            width: 14.28%;
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #f3f4f6;
            /* Lighter border */
            margin: -0.5px;
        }

        .cal-header {
            width: 14.28%;
            text-align: center;
            color: #64748b;
            padding: 4px 0;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-free {
            background-color: #fff;
            cursor: pointer;
            transition: background 0.2s;
        }

        .status-free:hover {
            background-color: #f0f9ff;
        }

        .status-reserved {
            background-color: #ef4444;
            /* Red-500 */
            color: white;
            cursor: not-allowed;
            background-image: repeating-linear-gradient(45deg,
                    transparent,
                    transparent 5px,
                    rgba(255, 255, 255, 0.1) 5px,
                    rgba(255, 255, 255, 0.1) 10px);
        }

        .status-selected {
            background-color: #0d9488;
            /* Teal-600 */
            color: white;
        }

        .status-indisponible {
            background-color: #94a3b8;
            color: white;
            cursor: not-allowed;
        }

        /* Custom scrollbar for iframe aesthetics if needed */
        ::-webkit-scrollbar {
            width: 4px;
            /* Thinner */
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    <script>
        // Simple selection logic
        let startDate = null;
        let endDate = null;

        function selectDate(date, status) {
            if (status === 'reserved') return;

            const cells = document.querySelectorAll('.cal-day');

            if (!startDate || (startDate && endDate)) {
                // New selection
                startDate = date;
                endDate = null;
                resetSelection();
                highlight(date, 'add');
            } else if (startDate && !endDate) {
                if (date < startDate) {
                    // Reset and start new
                    startDate = date;
                    resetSelection();
                    highlight(date, 'add');
                } else {
                    // Complete range
                    endDate = date;
                    highlightRange(startDate, endDate);
                    showBookingButton();
                }
            }
        }

        function resetSelection() {
            document.querySelectorAll('.status-selected').forEach(el => {
                el.classList.remove('status-selected');
                el.classList.add('status-free');
            });
            document.getElementById('booking-actions').classList.add('hidden');
        }

        function highlight(date, action) {
            const el = document.getElementById('day-' + date);
            if (el) {
                if (action === 'add') {
                    el.classList.remove('status-free');
                    el.classList.add('status-selected');
                }
            }
        }

        function highlightRange(start, end) {
            const startDt = new Date(start);
            const endDt = new Date(end);

            // Loop through all cells
            document.querySelectorAll('.cal-day[data-date]').forEach(cell => {
                const cellDate = cell.getAttribute('data-date');
                const currentDt = new Date(cellDate);
                if (currentDt >= startDt && currentDt <= endDt && !cell.classList.contains('status-reserved')) {
                    cell.classList.remove('status-free');
                    cell.classList.add('status-selected');
                }
            });
        }

        function showBookingButton() {
            const btnContainer = document.getElementById('booking-actions');
            const link = document.getElementById('book-link');
            if (startDate && endDate) {
                link.href = `reservation.php?checkin=${startDate}&checkout=${endDate}`;
                document.getElementById('dates-display').innerText = `Du ${startDate} au ${endDate}`;
                btnContainer.classList.remove('hidden');
            }
        }
    </script>
</head>

<body class="bg-white text-slate-800">

    <div class="w-full px-2 py-4 md:p-6 mx-auto">

        <header class="text-center mb-8 relative">
            <h1 class="text-3xl md:text-4xl font-bold mb-2 text-slate-900">Disponibilités & Tarifs</h1>
            <!-- Navigation -->
            <div class="flex items-center justify-center gap-6 mt-4 font-sans text-sm">
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>"
                    class="text-teal-600 hover:text-teal-800 transition flex items-center font-medium">
                    <span class="material-symbols-outlined text-lg mr-1">&laquo;</span>

                </a>

                <form action="" method="GET" class="inline-block relative">
                    <select name="month" onchange="this.form.submit()"
                        class="appearance-none bg-transparent font-bold text-lg cursor-pointer text-slate-700 hover:text-teal-600 transition pr-6 focus:outline-none">
                        <?php
                        $months_fr = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                        echo "<option value='$month' selected>{$months_fr[$month]}</option>";
                        foreach ($months_fr as $k => $m) {
                            if ($k != $month)
                                echo "<option value='$k'>$m</option>";
                        }
                        ?>
                    </select>
                    <span class="absolute right-0 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">▼</span>
                    <input type="hidden" name="year" value="<?php echo $year; ?>">
                </form>
                <span class="font-bold text-lg text-slate-700"><?php echo $year; ?></span>

                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>"
                    class="text-teal-600 hover:text-teal-800 transition flex items-center font-medium">
                    <span class="material-symbols-outlined text-lg ml-1">&raquo;</span>
                </a>
            </div>

            <!-- Legend -->
            <div
                class="flex items-center justify-center gap-4 mt-6 text-[10px] md:text-xs font-bold uppercase tracking-wider">
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-white border border-slate-300 rounded-sm"></span>
                    <span class="text-slate-500">Libre</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-red-500 rounded-sm opacity-90"></span>
                    <span class="text-slate-500">Réservé</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 bg-teal-600 rounded-sm"></span>
                    <span class="text-teal-700">Sélection</span>
                </div>
            </div>
        </header>

        <div class="flex flex-col xl:flex-row gap-8 items-start">

            <!-- LEFT: CALENDAR GRID (4 Months) -->
            <div class="flex-1 w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php
                    // Display 4 months starting from selected
                    for ($i = 0; $i < 4; $i++) {
                        $curr_m = $month + $i;
                        $curr_y = $year;
                        if ($curr_m > 12) {
                            $curr_m -= 12;
                            $curr_y++;
                        }
                        if ($curr_m > 24) {
                            $curr_m -= 12;
                            $curr_y++;
                        }

                        $month_name = $months_fr[$curr_m];
                        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $curr_m, $curr_y);
                        $first_day_of_week = date('N', strtotime("$curr_y-$curr_m-01")); // 1 (Mon) - 7 (Sun)
                    
                        echo "<div class='bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden'>";
                        echo "<div class='bg-slate-50 py-2 border-b border-slate-100'>";
                        echo "<h3 class='text-center font-bold text-slate-700 text-sm uppercase tracking-wide'>$month_name <span class='text-slate-400 font-normal'>$curr_y</span></h3>";
                        echo "</div>";

                        // Days Header
                        echo "<div class='flex bg-white py-1'>";
                        $headers = ['Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa', 'Di'];
                        foreach ($headers as $h)
                            echo "<div class='cal-header'>$h</div>";
                        echo "</div>";

                        echo "<div class='flex flex-wrap'>";

                        // Empty slots before 1st
                        for ($k = 1; $k < $first_day_of_week; $k++) {
                            echo "<div class='cal-day border-transparent bg-transparent'></div>";
                        }

                        // Days
                        for ($d = 1; $d <= $days_in_month; $d++) {
                            $date_str = sprintf("%04d-%02d-%02d", $curr_y, $curr_m, $d);
                            $status = getDayStatus($date_str, $reservations);
                            $class = $status == 'reserved' ? 'status-reserved' : 'status-free';
                            $onclick = $status == 'free' ? "onclick=\"selectDate('$date_str', '$status')\"" : "";

                            echo "<div id='day-$date_str' data-date='$date_str' class='cal-day $class' $onclick>$d</div>";
                        }
                        echo "</div>"; // End grid
                        echo "</div>"; // End month container
                    }
                    ?>
                </div>

                <!-- Booking Action Bar -->
                <div id="booking-actions"
                    class="hidden mt-8 sticky bottom-4 z-50 bg-white/95 backdrop-blur shadow-xl border border-teal-100 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 animate-fade-in-up">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-100 text-teal-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </span>
                        <div>
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Vos
                                dates</span>
                            <strong id="dates-display" class="text-slate-800"></strong>
                        </div>
                    </div>
                    <a id="book-link" href="#" target="_top"
                        class="bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-teal-500/30 transition-all transform hover:-translate-y-0.5">
                        Valider ma demande
                    </a>
                </div>
            </div>

            <!-- RIGHT: PRICING SIDEBAR -->
            <div class="w-full xl:w-80 shrink-0 bg-slate-50 rounded-xl p-6 border border-slate-200">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold tracking-widest text-slate-400 uppercase mb-1">Capacité</h4>
                        <span class="text-slate-800 font-bold text-lg">10 Personnes</span>
                    </div>
                    <div
                        class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="font-serif text-lg font-bold text-slate-800 mb-4 pb-2 border-b border-slate-200">Tarifs
                        Semaine 2026</h3>
                    <div class="space-y-3 text-sm text-slate-600">
                        <?php if (count($seasons) > 0): ?>
                            <?php foreach ($seasons as $saison): ?>
                                <div class="flex justify-between items-end">
                                    <span class="font-medium text-slate-700 bg-slate-50 relative z-10 pr-2">
                                        <?php echo formatDateFr($saison['date_debut']); ?> -
                                        <?php echo formatDateFr($saison['date_fin']); ?>
                                    </span>
                                    <span class="flex-1 border-b border-dotted border-slate-300 mb-1 mx-1"></span>
                                    <span class="font-bold text-slate-900 bg-slate-50 relative z-10 pl-2">
                                        <?php echo number_format($saison['prix_semaine'], 0); ?>€
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback -->
                            <div class="flex justify-between items-end"><span class="bg-slate-50 pr-2">Avril -
                                    Mai</span><span class="flex-1 border-b border-dotted border-slate-300 mb-1"></span><span
                                    class="bg-slate-50 pl-2 font-bold text-slate-900">1590€</span></div>
                            <div class="flex justify-between items-end"><span class="bg-slate-50 pr-2">Juin</span><span
                                    class="flex-1 border-b border-dotted border-slate-300 mb-1"></span><span
                                    class="bg-slate-50 pl-2 font-bold text-slate-900">1990€</span></div>
                            <div class="flex justify-between items-end"><span class="bg-slate-50 pr-2">Juillet -
                                    Août</span><span
                                    class="flex-1 border-b border-dotted border-slate-300 mb-1"></span><span
                                    class="bg-slate-50 pl-2 font-bold text-slate-900">4600€</span></div>
                            <div class="flex justify-between items-end"><span class="bg-slate-50 pr-2">Septembre</span><span
                                    class="flex-1 border-b border-dotted border-slate-300 mb-1"></span><span
                                    class="bg-slate-50 pl-2 font-bold text-slate-900">2990€</span></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-teal-50 rounded-lg p-4 mb-6 border border-teal-100">
                    <h4 class="font-bold text-slate-800 mb-1 flex items-center">
                        <span class="w-1.5 h-1.5 bg-teal-500 rounded-full mr-2"></span>
                        Week-end
                    </h4>
                    <p class="text-xs text-slate-500 italic mb-3 ml-3.5">(Selon période)</p>
                    <div class="ml-3.5">
                        <span class="block text-2xl font-serif font-bold text-teal-700">380 € <small
                                class="text-sm font-sans font-normal text-slate-500">/ nuit</small></span>
                        <span
                            class="text-xs font-semibold text-teal-600 bg-teal-100 px-2 py-0.5 rounded-full inline-block mt-1">3
                            nuits minimum</span>
                    </div>
                </div>

                <div class="text-xs text-slate-500 space-y-2 border-t border-slate-200 pt-4">
                    <p class="flex items-start"><span class="mr-2 text-slate-400">•</span> Paiement : Espèces, Virement
                    </p>
                    <p class="flex items-start"><span class="mr-2 text-slate-400">•</span> Acompte 30% à la réservation
                    </p>
                    <p class="flex items-start"><span class="mr-2 text-slate-400">•</span> Ménage fin de séjour :
                        <strong>220 €</strong>
                    </p>
                    <p class="flex items-start"><span class="mr-2 text-slate-400">•</span> Arrivée 17h / Départ 10h
                        (Samedi)</p>
                </div>

            </div>
        </div>

    </div>

</body>

</html>