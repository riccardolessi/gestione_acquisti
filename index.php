<?php
require_once 'db.php';
include 'includes/header.php';

// 1. Anni disponibili a database
$years = [];
$sqlYears = "SELECT DISTINCT YEAR(data_acquisto) as anno FROM movimenti ORDER BY anno DESC";
$resYears = $conn->query($sqlYears);
if ($resYears) {
    while ($row = $resYears->fetch_assoc()) {
        $years[] = $row['anno'];
    }
}

// Default: l'anno più recente presente, altrimenti l'anno corrente
$currentYear = date('Y');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : ($years[0] ?? $currentYear);

// 2. Dati dell'anno selezionato
$monthlyData = array_fill(1, 12, 0); // Inizializza i dodici mesi a 0

$sqlChart = "
    SELECT MONTH(data_acquisto) as mese_num, SUM(prezzo * quantita) as totale 
    FROM movimenti 
    WHERE YEAR(data_acquisto) = ? 
    GROUP BY mese_num
";
$stmt = $conn->prepare($sqlChart);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$resultChart = $stmt->get_result();

while ($row = $resultChart->fetch_assoc()) {
    $monthlyData[$row['mese_num']] = $row['totale'];
}

// Prepara i dati per Chart.js
$chartLabels = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
$chartValues = array_values($monthlyData);

?>

<div class="page-header">
    <h1 class="page-title">Dashboard Acquisti</h1>
    
    <!-- Selettore dell'anno -->
    <form action="index.php" method="GET" style="display: flex; align-items: center; gap: 10px;">
        <label for="year" style="margin: 0; font-weight: 500; color: var(--text-secondary);">Seleziona Anno:</label>
        <select name="year" id="year" onchange="this.form.submit()" style="min-width: 120px;">
            <?php foreach ($years as $y): ?>
                <option value="<?php echo $y; ?>" <?php echo ($selectedYear == $y) ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endforeach; ?>
            <?php if(empty($years)): ?>
                <option value="<?php echo $currentYear; ?>"><?php echo $currentYear; ?></option>
            <?php endif; ?>
        </select>
    </form>
</div>

<!-- Grafico dell'andamento mensile -->
<div class="card" style="min-height: 500px; display: flex; flex-direction: column;">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 1.25rem;">Andamento Spese - Anno <?php echo $selectedYear; ?></h2>
        <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">
            Totale: € <?php echo number_format(array_sum($chartValues), 2, ',', '.'); ?>
        </div>
    </div>
    
    <div class="chart-container-wrapper" style="flex: 1; min-height: 400px;">
        <canvas id="expensesChart"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('expensesChart').getContext('2d');
const expensesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Spesa (€)',
            data: <?php echo json_encode($chartValues); ?>,
            backgroundColor: 'rgba(56, 189, 248, 0.2)',
            borderColor: '#38bdf8',
            borderWidth: 2,
            pointBackgroundColor: '#0ea5e9',
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                titleColor: '#f1f5f9',
                bodyColor: '#cbd5e1',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(148, 163, 184, 0.1)',
                    borderDash: [5, 5]
                },
                ticks: {
                    color: '#94a3b8',
                    callback: function(value) { return '€ ' + value; }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#94a3b8'
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>