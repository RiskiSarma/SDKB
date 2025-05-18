<?php
// view_report.php
include "proses/connect.php";
require_once "proses/report_functions.php";

// Get report ID from URL parameter
$report_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get the report data
$report = getReportById($report_id);

// If report not found, show error
if (!$report) {
    echo '<div class="col-lg-9 mt-2">
            <div class="alert alert-danger">
                Laporan tidak ditemukan.
            </div>
          </div>';
    exit;
}
?>

<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>
                <?php echo $report['type'] === 'monthly' ? 'Laporan Bulanan' : 'Laporan Semester'; ?> - 
                <?php echo htmlspecialchars($report['period']); ?>
            </h4>
            <div>
                <button class="btn btn-primary" id="printReport">
                    <i class="bi bi-printer"></i> Cetak
                </button>
                <a href="?page=laporan" class="btn btn-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="card-body">
            <div id="reportContent">
                <?php echo $report['content']; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const printReportBtn = document.getElementById('printReport');
    if (printReportBtn) {
        printReportBtn.addEventListener('click', function() {
            const printContents = document.getElementById('reportContent').innerHTML;
            const originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div style="padding: 20px;">
                    ${printContents}
                </div>
            `;
            
            window.print();
            
            document.body.innerHTML = originalContents;
            window.location.reload();
        });
    }
});
</script>